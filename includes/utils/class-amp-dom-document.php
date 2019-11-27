<?php
/**
 * Class AMP_DOM_Document.
 *
 * @package AMP
 */

/**
 * Class AMP_DOM_Document.
 *
 * @since 1.5
 *
 * @property DOMXPath   $xpath XPath query object for this document.
 * @property DOMElement $head  The document's <head> element.
 * @property DOMElement $body  The document's <body> element.
 *
 * Abstract away some of the difficulties of working with PHP's DOMDocument.
 */
final class AMP_DOM_Document extends DOMDocument {

	/**
	 * AMP requires the HTML markup to be encoded in UTF-8.
	 */
	const AMP_ENCODING = 'utf-8';

	/**
	 * Encoding identifier to use for an unknown encoding.
	 *
	 * "auto" is recognized by mb_convert_encoding() as a special value.
	 */
	const UNKNOWN_ENCODING = 'auto';

	/**
	 * Encoding detection order in case we have to guess.
	 *
	 * This list of encoding detection order is just a wild guess and might need fine-tuning over time.
	 * If the charset was not provided explicitly, we can really only guess, as the detection can
	 * never be 100% accurate and reliable.
	 */
	const ENCODING_DETECTION_ORDER = 'UTF-8, EUC-JP, eucJP-win, JIS, ISO-2022-JP, ISO-8859-15, ISO-8859-1, ASCII';

	/**
	 * Regular expression pattern to match the http-equiv meta tag.
	 */
	const HTTP_EQUIV_META_TAG_PATTERN = '/<meta [^>]*?\s*http-equiv=[^>]*?>[^<]*(?:<\/meta>)?/i';

	/**
	 * Regular expression pattern to match the charset meta tag.
	 */
	const CHARSET_META_TAG_PATTERN = '/<meta [^>]*?\s*charset=[^>]*?>[^<]*(?:<\/meta>)?/i';

	/**
	 * Regular expression pattern to match the main HTML structural tags.
	 */
	const HTML_STRUCTURE_PATTERN = '/(?:.*?(?<doctype><!doctype(?:\s+[^>]*)?>))?(?:(?<pre_html>.*?)(?<html_start><html(?:\s+[^>]*)?>))?(?:.*?(?<head><head(?:\s+[^>]*)?>.*?<\/head\s*>))?(?:.*?(?<body><body(?:\s+[^>]*)?>.*?<\/body\s*>))?.*?(?:(?:.*(?<html_end><\/html\s*>)|.*)(?<post_html>.*))/is';

	/**
	 * ID of the hacky charset we need to add to make loadHTML() behave.
	 */
	const CHARSET_HACK_ID = '--amp-dom-document-charset--';

	/**
	 * The original encoding of how the AMP_DOM_Document was created.
	 *
	 * This is stored to do an automatic conversion to UTF-8, which is
	 * a requirement for AMP.
	 *
	 * @var string
	 */
	private $original_encoding;

	/**
	 * Associative array of encoding mappings.
	 *
	 * Translates HTML charsets into encodings PHP can understand.
	 *
	 * @var string[]
	 */
	private $encoding_map = [
		// Assume ISO-8859-1 for some charsets.
		'latin-1' => 'ISO-8859-1',
	];

	/**
	 * Creates a new AMP_DOM_Document object
	 *
	 * @link  https://php.net/manual/domdocument.construct.php
	 *
	 * @param string $version  Optional. The version number of the document as part of the XML declaration.
	 * @param string $encoding Optional. The encoding of the document as part of the XML declaration.
	 */
	public function __construct( $version = '', $encoding = null ) {
		$this->original_encoding = (string) $encoding ?: self::UNKNOWN_ENCODING;
		parent::__construct( $version ?: '1.0', self::AMP_ENCODING );
	}

	/**
	 * Load HTML from a string.
	 *
	 * @link  https://php.net/manual/domdocument.loadhtml.php
	 *
	 * @param string     $source  The HTML string.
	 * @param int|string $options Optional. Specify additional Libxml parameters.
	 * @return bool true on success or false on failure.
	 */
	public function loadHTML( $source, $options = 0 ) {
		$source = $this->normalize_document_structure( $source );

		$this->original_encoding = $this->detect_and_strip_encoding( $source );

		if ( self::AMP_ENCODING !== strtolower( $this->original_encoding ) ) {
			$source = $this->adapt_encoding( $source );
		}

		// Force-add http-equiv charset to make DOMDocument behave as it should.
		// See: http://php.net/manual/en/domdocument.loadhtml.php#78243.
		$source = preg_replace(
			'/<head(?:\s+[^>]*)?>/i',
			'$1<meta http-equiv="content-type" content="text/html; charset=' . self::AMP_ENCODING . '">',
			$source,
			1
		);

		$success = parent::loadHTML( $source, $options );

		if ( $success ) {
			// Remove http-equiv charset again.
			$head = $this->getElementsByTagName( 'head' )->item( 0 );
			$meta = $head->firstChild;
			if ( 'meta' === $meta->tagName && 'content-type' === $meta->getAttribute( 'http-equiv' ) && ( 'text/html; charset=' . self::AMP_ENCODING ) === $meta->getAttribute( 'content' ) ) {
				$head->removeChild( $meta );
			}
		}

		return $success;
	}

	/**
	 * Dumps the internal document into a string using HTML formatting.
	 *
	 * @link  https://php.net/manual/domdocument.savehtml.php
	 *
	 * @param DOMNode $node Optional. Parameter to output a subset of the document.
	 * @return string The HTML, or false if an error occurred.
	 */
	public function saveHTML( DOMNode $node = null ) {
		// Force-add http-equiv charset to make DOMDocument behave as it should.
		// See: http://php.net/manual/en/domdocument.loadhtml.php#78243.
		$charset = AMP_DOM_Utils::create_node(
			$this,
			'meta',
			[
				'http-equiv' => 'content-type',
				'content'    => 'text/html; charset=' . self::AMP_ENCODING,
			]
		);
		$this->head->insertBefore( $charset, $this->head->firstChild );

		return preg_replace(
			sprintf(
				'#<meta http-equiv=([\'"])content-type\1 content=([\'"])text/html; charset=%s\2>#i',
				preg_quote( self::AMP_ENCODING, '#' )
			),
			'',
			parent::saveHTML( $node ),
			1
		);
	}

	/**
	 * Normalize the document structure.
	 *
	 * This makes sure the document adheres to the general structure that AMP requires:
	 *   ```
	 *   <!doctype html>
	 *   <html>
	 *     <head>
	 *       <meta charset="utf-8">
	 *     </head>
	 *     <body>
	 *     </body>
	 *   </html>
	 *   ```
	 *
	 * @param string $content Content to normalize the structure of.
	 * @return string Normalized content.
	 */
	private function normalize_document_structure( $content ) {
		$matches = [];

		// Unable to parse, so skip normalization and hope for the best.
		if ( false === preg_match( self::HTML_STRUCTURE_PATTERN, $content, $matches ) ) {
			return $content;
		}

		// Strip doctype for now.
		if ( ! empty( $matches['doctype'] ) ) {
			$content = preg_replace(
				sprintf(
					'/^.*?%s/s',
					str_replace( "\n", '\R', preg_quote( $matches['doctype'], '/' ) )
				),
				'',
				$content,
				1
			);
		}

		if ( empty( $matches['head'] ) && empty( $matches['body'] ) ) {
			// Neither body, nor head, so wrap content in both.
			$pattern = sprintf(
				'/%s(.*)%s/is',
				( empty( $matches['html_start'] ) ? '^\s*' : preg_quote( $matches['html_start'], '/' ) ),
				( empty( $matches['html_end'] ) ? '$\s*' : preg_quote( $matches['html_end'], '/' ) )
			);
			$content = preg_replace(
				$pattern,
				( empty( $matches['html_start'] ) ? '' : $matches['html_start'] )
					. '<head></head><body>$1</body>'
					. ( empty( $matches['html_end'] ) ? '' : $matches['html_end'] ),
				$content,
				1
			);
		} elseif ( empty( $matches['body'] ) && ! empty( $matches['head'] ) ) {
			// Head without body, so wrap content in body.
			$pattern = sprintf(
				'/%s(.*)%s/is',
				preg_quote( $matches['head'], '/' ),
				( empty( $matches['html_end'] ) ? '' : preg_quote( $matches['html_end'], '/' ) )
			);
			$content = preg_replace(
				$pattern,
				$matches['head']
					. '<body>$1</body>'
					. ( empty( $matches['html_end'] ) ? '' : $matches['html_end'] ),
				$content,
				1
			);
		} elseif ( empty( $matches['head'] ) && ! empty( $matches['body'] ) ) {
			// Body without head, so add empty head before body.
			$content = str_replace( $matches['body'], '<head></head>' . $matches['body'], $content );
		}

		if ( empty( $matches['html_start'] ) ) {
			// No surround html tag, so wrap the content in html.
			$content = "<html>{$content}</html>";
		}

		// Reinsert a standard doctype.
		$content = "<!DOCTYPE html>{$content}";

		return $content;
	}

	/**
	 * Adapt the encoding of the content.
	 *
	 * @param string $source Source content to adapt the encoding of.
	 * @return string Adapted content.
	 */
	private function adapt_encoding( $source ) {
		// No encoding was provided, so we need to guess.
		if ( self::UNKNOWN_ENCODING === $this->original_encoding && function_exists( 'mb_detect_encoding' ) ) {
			$this->original_encoding = mb_detect_encoding( $source, self::ENCODING_DETECTION_ORDER, true );
		}

		// Guessing the encoding seems to have failed, so we assume UTF-8 instead.
		if ( empty( $this->original_encoding ) ) {
			$this->original_encoding = self::AMP_ENCODING;
		}

		$this->original_encoding = $this->sanitize_encoding( $this->original_encoding );

		$target = false;
		if ( self::AMP_ENCODING !== strtolower( $this->original_encoding ) ) {
			$target = function_exists( 'mb_convert_encoding' )
				? mb_convert_encoding( $source, self::AMP_ENCODING, $this->original_encoding )
				: false;
		}

		return false !== $target ? $target : $source;
	}

	/**
	 * Detect the encoding of the document.
	 *
	 * @param string $content Content of which to detect the encoding.
	 * @return string|false Detected encoding of the document, or false if none.
	 */
	private function detect_and_strip_encoding( &$content ) {
		$encoding = self::UNKNOWN_ENCODING;

		// Check for HTML 4 http-equiv meta tags.
		$http_equiv_tag = $this->find_tag( $content, 'meta', 'http-equiv' );
		if ( $http_equiv_tag ) {
			$encoding = $this->extract_value( $http_equiv_tag, 'charset' );
		}

		// Check for HTML 5 charset meta tag. This overrides the HTML 4 charset.
		$charset_tag = $this->find_tag( $content, 'meta', 'charset' );
		if ( $charset_tag ) {
			$encoding = $this->extract_value( $charset_tag, 'charset' );
		}

		// Strip charset tags if they don't fit the AMP UTF-8 requirement.
		if ( self::AMP_ENCODING !== strtolower( $encoding ) ) {
			if ( $http_equiv_tag ) {
				$content = str_replace( $http_equiv_tag, '', $content );
			}

			if ( $charset_tag ) {
				$content = str_replace( $charset_tag, '', $content );
			}
		}

		return $encoding;
	}

	/**
	 * Find a given tag with a given attribute.
	 *
	 * If multiple tags match, this method will only return the first one.
	 *
	 * @param string $content   Content in which to find the tag.
	 * @param string $element   Element of the tag.
	 * @param string $attribute Attribute that the tag contains.
	 * @return string|false The requested tag, or false if not found.
	 */
	private function find_tag( $content, $element, $attribute = null ) {
		$matches = [];
		$pattern = empty( $attribute )
			? sprintf(
				'/<%1$s[^>]*?>[^<]*(?:<\/%1$s>)?/i',
				preg_quote( $element, '/' )
			)
			: sprintf(
				'/<%1$s [^>]*?\s*%2$s=[^>]*?>[^<]*(?:<\/%1$s>)?/i',
				preg_quote( $element, '/' ),
				preg_quote( $attribute, '/' )
			);

		if ( preg_match( $pattern, $content, $matches ) ) {
			return $matches[0];
		}

		return false;
	}

	/**
	 * Extract an attribute value from an HTML tag.
	 *
	 * @param string $tag       Tag from which to extract the attribute.
	 * @param string $attribute Attribute of which to extract the value.
	 * @return string|false Extracted attribute value, false if not found.
	 */
	private function extract_value( $tag, $attribute ) {
		$matches = [];
		$pattern = sprintf(
			'/%s=(?:([\'"])(?<full>.*)?\1|(?<partial>[^ \'";]+))/',
			preg_quote( $attribute, '/' )
		);

		if ( preg_match( $pattern, $tag, $matches ) ) {
			return empty( $matches['full'] ) ? $matches['partial'] : $matches['full'];
		}

		return false;
	}

	/**
	 * Sanitize the encoding that was detected.
	 *
	 * If sanitization fails, it will return 'auto', letting the conversion
	 * logic try to figure it out itself.
	 *
	 * @param string $encoding Encoding to sanitize.
	 * @return string Sanitized encoding. Falls back to 'auto' on failure.
	 */
	private function sanitize_encoding( $encoding ) {
		if ( ! function_exists( 'mb_list_encodings' ) ) {
			return $encoding;
		}

		static $known_encodings = null;

		if ( null === $known_encodings ) {
			$known_encodings = array_map( 'strtolower', mb_list_encodings() );
		}

		if ( array_key_exists( strtolower( $encoding ), $this->encoding_map ) ) {
			$encoding = $this->encoding_map[ strtolower( $encoding ) ];
		}

		if ( ! in_array( strtolower( $encoding ), $known_encodings, true ) ) {
			return self::UNKNOWN_ENCODING;
		}

		return $encoding;
	}

	/**
	 * Magic getter to implement lazily-created, cached properties for the document.
	 *
	 * @param string $name Name of the property to get.
	 * @return mixed Value of the property, or null if unknown property was requested.
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'xpath':
				$this->xpath = new DOMXPath( $this );
				return $this->xpath;
			case 'head':
				$this->head = $this->getElementsByTagName( 'head' )->item( 0 );
				return $this->head;
			case 'body':
				$this->body = $this->getElementsByTagName( 'body' )->item( 0 );
				return $this->body;
		}

		// Mimic regular PHP behavior for missing notices.
		trigger_error( "Undefined property: AMP_DOM_Document::${$name}", E_NOTICE ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions,WordPress.Security.EscapeOutput
		return null;
	}
}
