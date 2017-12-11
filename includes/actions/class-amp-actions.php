<?php

abstract class AMP_Actions {
	public static function add_scripts( $template ) {}
	public static function add_styles( $template ) {}
	public static function add_fonts( $template ) {}
	public static function add_boilerplate_css( $template ) {}
	public static function add_schemaorg_metadata( $template ) {}
	public static function add_analytics_scripts( $template ) {}
	public static function add_analytics_data( $template ) {}
	public static function add_canonical_link( $template ) {}

	/**
	 * Add AMP generator metadata.
	 *
	 * @param object $template AMP_Post_Template object.
	 * @since 0.6
	 */
	public static function add_generator_metadata( $template ) {}
}
