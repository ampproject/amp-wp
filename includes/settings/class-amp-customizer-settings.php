<?php

class AMP_Customizer_Settings {
	const DEFAULT_HEADER_COLOR = '#fff';
	const DEFAULT_HEADER_BACKGROUND_COLOR = '#0a89c0';
	const DEFAULT_COLOR_SCHEME = 'light';

	private static function get_stored_options() {
		return get_option( 'amp_customizer', array() );
	}

	public static function get_settings() {
		$settings = self::get_stored_options();
		$settings = wp_parse_args( $settings, array(
			'header_color' => self::DEFAULT_HEADER_COLOR,
			'header_background_color' => self::DEFAULT_HEADER_BACKGROUND_COLOR,
			'color_scheme' => self::DEFAULT_COLOR_SCHEME,
		) );

		$theme_colors = self::get_colors_for_color_scheme( $settings['color_scheme'] );

		return array_merge( $settings, $theme_colors, array(
			'link_color' => $settings['header_background_color'],
		) );
	}

	public static function get_colors_for_color_scheme( $scheme ) {
		switch ( $scheme ) {
			case 'dark':
				return array(
					// Convert and invert colors to greyscale for dark theme color; see http://goo.gl/uVB2cO
					'theme_color'      => '#111',
					'text_color'       => '#acacac',
					'muted_text_color' => '#606060',
					'border_color'     => '#2b2b2b',
				);

			case 'light':
			default:
				return array(
					// Convert colors to greyscale for light theme color; see http://goo.gl/2gDLsp
					'theme_color'      => '#fff',
					'text_color'       => '#535353',
					'muted_text_color' => '#9f9f9f',
					'border_color'     => '#d4d4d4',
				);
		}
	}
}
