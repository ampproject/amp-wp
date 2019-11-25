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
 * @property DOMXPath $xpath XPath query object for this document.
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
	 */
	const UNKNOWN_ENCODING = 'auto';

	/**
	 * Regular expression pattern to match the http-equiv meta tag.
	 */
	const HTTP_EQUIV_META_TAG_PATTERN = '/<meta [^>]*?\s*http-equiv=[^>]*?>[^<]*(?:<\/meta>)?/i';

	/**
	 * Regular expression pattern to match the charset meta tag.
	 */
	const CHARSET_META_TAG_PATTERN = '/<meta [^>]*?\s*charset=[^>]*?>[^<]*(?:<\/meta>)?/i';

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
		'latin-1' => 'iso-8859-1',
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
		$source = $this->maybe_add_head_or_body( $source );

		$this->original_encoding = $this->detect_and_strip_encoding( $source );

		if ( self::AMP_ENCODING !== strtolower( $this->original_encoding ) ) {
			$source = $this->adapt_encoding( $source );
		}

		// Force-add http-equiv charset to make DOMDocument behave as it should.
		// See: http://php.net/manual/en/domdocument.loadhtml.php#78243.
		$source = str_replace(
			'<head>',
			'<head><meta http-equiv="content-type" content="text/html; charset=' . self::AMP_ENCODING . '">',
			$source
		);

		$success = parent::loadHTML( $source, $options );

		if ( $success ) {
			$head = $this->getElementsByTagName( 'head' )->item( 0 );
			$head->removeChild( $head->firstChild );
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
		$head    = $this->getElementsByTagName( 'head' )->item( 0 );
		$charset = AMP_DOM_Utils::create_node(
			$this,
			'meta',
			[
				'http-equiv' => 'content-type',
				'content'    => 'text/html; charset=' . self::AMP_ENCODING,
			]
		);
		$head->insertBefore( $charset, $head->firstChild );

		return str_replace( '<meta http-equiv="content-type" content="text/html; charset=' . self::AMP_ENCODING . '">', '', parent::saveHTML( $node ) );
	}

	/**
	 * Maybe add the <head> and/or <body> tag(s).
	 *
	 * @param string $content Content to add the head or body tags to.
	 * @return string Adapted content.
	 */
	private function maybe_add_head_or_body( $content ) {
		$substring = substr( $content, 0, 5000 );
		if ( false === strpos( $substring, '<body' ) ) {
			if ( false === strpos( $substring, '<head>' ) ) {
				// Create the required HTML structure if none exists yet.
				$content = "<html><head></head><body>{$content}</body></html>";
			} else {
				// <head> seems to be present without <body>.
				$content = preg_replace( '#</head>(.*)</html>#', '</head><body>$1</body>', $content );
			}
		} elseif ( false === strpos( $substring, '<head>' ) ) {
			// Create a <head> element if none exists yet.
			$content = str_replace( '<body', '<head></head><body', $content );
		}

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
			$this->original_encoding = mb_detect_encoding( $source );
		}

		// Guessing the encoding seems to have failed, so we assume UTF-8 instead.
		if ( empty( $this->original_encoding ) ) {
			$this->original_encoding = self::AMP_ENCODING;
		}

		$this->original_encoding = $this->sanitize_encoding( $this->original_encoding );

		$target = function_exists( 'mb_convert_encoding' ) ? mb_convert_encoding( $source, self::AMP_ENCODING, $this->original_encoding ) : false;

		return is_string( $target ) ? $target : $source;
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
			$http_equiv_tag && str_replace( $http_equiv_tag, '', $content );
			$charset_tag && str_replace( $charset_tag, '', $content );
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

		if ( array_key_exists( $encoding, $this->encoding_map ) ) {
			$encoding = $this->encoding_map[ $encoding ];
		}

		if ( ! in_array( $encoding, $known_encodings, true ) ) {
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
		}

		// Mimic regular PHP behavior for missing notices.
		trigger_error( "Undefined property: AMP_DOM_Document::${$name}", E_NOTICE ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions,WordPress.Security.EscapeOutput
		return null;
	}
}
