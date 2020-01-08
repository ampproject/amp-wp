<?php
/**
 * Class AMP_Fonts
 *
 * @package AMP
 */

/**
 * Class AMP_Fonts
 */
class AMP_Fonts {
	/**
	 * The URL of Google fonts.
	 *
	 * @var string
	 */
	const URL = 'https://fonts.googleapis.com/css';

	/**
	 * Get list of fonts used in AMP Stories.
	 *
	 * @return array Fonts.
	 */
	public static function get_fonts() {
		static $fonts = null;

		if ( isset( $fonts ) ) {
			return $fonts;
		}

		$default_weight = [ '400', '700' ];

		// Default system fonts.
		$fonts = [
			[
				'name'      => 'Arial',
				'fallbacks' => [ 'Helvetica Neue', 'Helvetica', 'sans-serif' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Arial Black',
				'fallbacks' => [ 'Arial Black', 'Arial Bold', 'Gadget', 'sans-serif' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Arial Narrow',
				'fallbacks' => [ 'Arial', 'sans-serif' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Baskerville',
				'fallbacks' => [ 'Baskerville Old Face', 'Hoefler Text', 'Garamond', 'Times New Roman', 'serif' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Brush Script MT',
				'fallbacks' => [ 'cursive' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Copperplate',
				'fallbacks' => [ 'Copperplate Gothic Light', 'fantasy' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Courier New',
				'fallbacks' => [ 'Courier', 'Lucida Sans Typewriter', 'Lucida Typewriter', 'monospace' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Century Gothic',
				'fallbacks' => [ 'CenturyGothic', 'AppleGothic', 'sans-serif' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Garamond',
				'fallbacks' => [ 'Baskerville', 'Baskerville Old Face', 'Hoefler Text', 'Times New Roman', 'serif' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Georgia',
				'fallbacks' => [ 'Times', 'Times New Roman', 'serif' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Gill Sans',
				'fallbacks' => [ 'Gill Sans MT', 'Calibri', 'sans-serif' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Lucida Bright',
				'fallbacks' => [ 'Georgia', 'serif' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Lucida Sans Typewriter',
				'fallbacks' => [ 'Lucida Console', 'monaco', 'Bitstream Vera Sans Mono', 'monospace' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Palatino',
				'fallbacks' => [ 'Palatino Linotype', 'Palatino LT STD', 'Book Antiqua', 'Georgia', 'serif' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Papyrus',
				'fallbacks' => [ 'fantasy' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Tahoma',
				'fallbacks' => [ 'Verdana', 'Segoe', 'sans-serif' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Times New Roman',
				'fallbacks' => [ 'Times New Roman', 'Times', 'Baskerville', 'Georgia', 'serif' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Trebuchet MS',
				'fallbacks' => [ 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', 'Tahoma', 'sans-serif' ],
				'weights'   => $default_weight,
			],
			[
				'name'      => 'Verdana',
				'fallbacks' => [ 'Geneva', 'sans-serif' ],
				'weights'   => $default_weight,
			],
		];
		$file  = __DIR__ . '/data/fonts.json';
		$fonts = array_merge( $fonts, self::get_google_fonts( $file ) );

		$columns = wp_list_pluck( $fonts, 'name' );
		array_multisort( $columns, SORT_ASC, $fonts );

		$fonts_url = self::URL;
		$subsets   = self::get_subsets();

		$fonts = array_map(
			static function ( $font ) use ( $fonts_url, $subsets ) {
				$font['slug'] = sanitize_title( $font['name'] );

				if ( ! empty( $font['gfont'] ) ) {
					$font['handle'] = sprintf( '%s-font', $font['slug'] );
					$font['src']    = add_query_arg(
						[
							'family'  => rawurlencode( $font['gfont'] ),
							'subset'  => rawurlencode( implode( ',', $subsets ) ),
							'display' => 'swap',
						],
						$fonts_url
					);
				}

				return $font;
			},
			$fonts
		);

		return $fonts;
	}

	/**
	 * Get subsets of fonts based on language settings.
	 *
	 * @return array
	 */
	public static function get_subsets() {
		$subsets = [ 'latin', 'latin-ext' ];

		/*
		 * Translators: To add an additional character subset specific to your language,
		 * translate this to 'greek', 'cyrillic', 'devanagari' or 'vietnamese'. Do not translate into your own language.
		 */
		$subset = _x( 'no-subset', 'Add new subset (greek, cyrillic, devanagari, vietnamese)', 'amp' );

		switch ( $subset ) {
			case 'cyrillic':
				$subsets[] = 'cyrillic';
				$subsets[] = 'cyrillic-ext';
				break;
			case 'greek':
				$subsets[] = 'greek';
				$subsets[] = 'greek-ext';
				break;
			case 'devanagari':
				$subsets[] = 'devanagari';
				break;
			case 'vietnamese':
				$subsets[] = 'vietnamese';
				break;
		}

		return $subsets;
	}

	/**
	 * Get list of Google Fonts from a given JSON file.
	 *
	 * @param string $file Path to file containing Google Fonts definitions.
	 *
	 * @return array $fonts Fonts list.
	 */
	public static function get_google_fonts( $file ) {
		if ( ! is_readable( $file ) ) {
			return [];
		}
		$file_content = file_get_contents( $file );  // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$google_fonts = json_decode( $file_content, true );

		if ( empty( $google_fonts ) ) {
			return [];
		}

		$fonts = [];

		foreach ( $google_fonts as $font ) {

			$variants = array_map(
				static function ( $variant ) {
					$variant = str_replace(
						[ '0italic', 'regular', 'italic' ],
						[ '0i', '400', '400i' ],
						$variant
					);

					return $variant;
				},
				$font['variants']
			);

			$gfont = '';

			if ( $variants ) {
				$gfont = $font['family'] . ':' . implode( ',', $variants );
			}

			$weights = [];
			foreach ( $font['variants'] as $variant ) {
				$variant   = str_replace( 'italic', '', $variant );
				$variant   = str_replace( 'regular', '400', $variant );
				$weights[] = $variant;
			}

			$weights = array_unique( $weights );
			$weights = array_filter( $weights );
			$weights = array_values( $weights );

			$fonts[] = [
				'name'      => $font['family'],
				'fallbacks' => (array) self::get_font_fallback( $font['category'] ),
				'gfont'     => $gfont,
				'weights'   => $weights,
			];
		}

		return $fonts;
	}

	/**
	 * Helper method to lookup fallback font.
	 *
	 * @param string $category Google font category.
	 *
	 * @return string $fallback Fallback font.
	 */
	public static function get_font_fallback( $category ) {
		switch ( $category ) {
			case 'sans-serif':
				return 'sans-serif';
			case 'handwriting':
			case 'display':
				return 'cursive';
			case 'monospace':
				return 'monospace';
			default:
				return 'serif';
		}
	}

	/**
	 * Get a font.
	 *
	 * @param string $name Font family name.
	 *
	 * @return array|null The font or null if not defined.
	 */
	public static function get_font( $name ) {
		$fonts = array_filter(
			self::get_fonts(),
			static function ( $font ) use ( $name ) {
				return $font['name'] === $name;
			}
		);

		return array_shift( $fonts );
	}

}
