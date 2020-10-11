<?php
/**
 * Class AMP_Emoji_Sanitizer
 *
 * @since 2.1.0
 * @package AMP
 */

/**
 * Class AMP_Emoji_Sanitizer
 *
 * @since 2.1.0
 * @internal
 */
class AMP_Emoji_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * CDN URL.
	 *
	 * @todo Replace this with what is in the current version of WordPress core.
	 *
	 * @var string
	 */
	const CDN_URL = 'https://s.w.org/images/core/emoji/13.0.0/svg/';

	/**
	 * Emoji lookup.
	 *
	 * Mapping of UTF-8 emoji characters to their Twemoji image basenames (without file extension).
	 *
	 * @var array
	 */
	private $emoji_lookup = [];

	/**
	 * Regular expression to match a UTF-8 emoji character.
	 *
	 * @todo At build time, replace with pre-computed regex.
	 *
	 * @var string
	 */
	private $emoji_regex = '';

	/**
	 * Replace UTF-8 emoji with Twemoji SVG.
	 *
	 * @see wp_staticize_emoji()
	 */
	public function sanitize() {
		if ( empty( $this->emoji_lookup ) || empty( $this->emoji_regex ) ) {
			$this->build_emoji_data();
		}

		$query = $this->dom->xpath->query( './/text()', $this->dom->body );
		foreach ( $query as $text_node ) {
			/** @var DOMText $text_node */

			if ( trim( $text_node->nodeValue ) === '' ) {
				continue;
			}

			// Ignore processing of specific tags.
			if ( in_array( $text_node->parentNode->nodeName, [ 'code', 'pre', 'style', 'script', 'textarea' ], true ) ) {
				continue;
			}

			$parts = preg_split( "/({$this->emoji_regex})/u", $text_node->nodeValue, -1, PREG_SPLIT_DELIM_CAPTURE );
			if ( 1 === count( $parts ) ) {
				continue;
			}

			$fragment = $this->dom->createDocumentFragment();

			foreach ( $parts as $part ) {
				if ( '' === $part ) {
					continue;
				}

				if ( isset( $this->emoji_lookup[ $part ] ) ) {
					$svg_url = self::CDN_URL . $this->emoji_lookup[ $part ] . '.svg';

					// @todo Try to fetch the SVG and store in a transient, and then load it inline.
					$img_element = $this->dom->createElement( 'img' );
					$img_element->setAttribute( 'class', 'emoji' );
					$img_element->setAttribute( 'alt', $part );
					$img_element->setAttribute( 'draggable', 'false' );
					$img_element->setAttribute( 'src', $svg_url );
					$img_element->setAttribute( 'width', '72' );
					$img_element->setAttribute( 'height', '72' );
					$fragment->appendChild( $img_element );
				} else {
					$fragment->appendChild( $this->dom->createTextNode( $part ) );
				}
			}
			$text_node->parentNode->replaceChild( $fragment, $text_node );
		}
	}

	/**
	 * Build emoji data.
	 *
	 * This is used during development only.
	 */
	private function build_emoji_data() {
		$this->emoji_lookup = [];
		$this->emoji_regex  = '';
		foreach ( _wp_emoji_list() as $emoji_entity ) {
			$basename = rtrim(
				str_replace(
					[ '&', '#', 'x', ';' ],
					[ '', '', '', '-' ],
					$emoji_entity
				),
				'-'
			);

			$emojum = html_entity_decode( $emoji_entity, ENT_NOQUOTES, 'utf-8' );

			$this->emoji_lookup[ $emojum ] = $basename;

			if ( $this->emoji_regex ) {
				$this->emoji_regex .= '|';
			}
			$this->emoji_regex .= preg_quote( $emojum, '/' );
		}
	}
}
