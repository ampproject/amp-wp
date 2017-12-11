<?php
/**
 * Class AMP_Content_Sanitizer
 *
 * @package AMP
 */

/**
 * Class AMP_Content_Sanitizer
 */
class AMP_Content_Sanitizer {

	/**
	 * Sanitize.
	 *
	 * @param string   $content           Content.
	 * @param string[] $sanitizer_classes Sanitizer classes.
	 * @param array    $global_args       Global args.
	 *
	 * @return array
	 */
	public static function sanitize( $content, array $sanitizer_classes, $global_args = array() ) {
		$scripts = array();
		$styles  = array();
		$dom     = AMP_DOM_Utils::get_dom_from_content( $content );

		foreach ( $sanitizer_classes as $sanitizer_class => $args ) {
			if ( ! class_exists( $sanitizer_class ) ) {
				/* translators: %s is sanitizer class */
				_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'Sanitizer (%s) class does not exist', 'amp' ), esc_html( $sanitizer_class ) ), '0.4.1' );
				continue;
			}

			$sanitizer = new $sanitizer_class( $dom, array_merge( $global_args, $args ) );

			if ( ! is_subclass_of( $sanitizer, 'AMP_Base_Sanitizer' ) ) {
				/* translators: %s is sanitizer class */
				_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'Sanitizer (%s) must extend `AMP_Base_Sanitizer`', 'amp' ), esc_html( $sanitizer_class ) ), '0.1' );
				continue;
			}

			$sanitizer->sanitize();

			$scripts = array_merge( $scripts, $sanitizer->get_scripts() );
			$styles  = array_merge( $styles, $sanitizer->get_styles() );
		}

		$sanitized_content = AMP_DOM_Utils::get_content_from_dom( $dom );

		return array( $sanitized_content, $scripts, $styles );
	}
}

