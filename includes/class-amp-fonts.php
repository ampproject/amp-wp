<?php


class AMP_Fonts {
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

		// Default system fonts.
		$fonts = [
			[
				'name'      => 'Arial',
				'fallbacks' => [ 'Helvetica Neue', 'Helvetica', 'sans-serif' ],
			],
			[
				'name'      => 'Arial Black',
				'fallbacks' => [ 'Arial Black', 'Arial Bold', 'Gadget', 'sans-serif' ],
			],
			[
				'name'      => 'Arial Narrow',
				'fallbacks' => [ 'Arial', 'sans-serif' ],
			],
			[
				'name'      => 'Baskerville',
				'fallbacks' => [ 'Baskerville Old Face', 'Hoefler Text', 'Garamond', 'Times New Roman', 'serif' ],
			],
			[
				'name'      => 'Brush Script MT',
				'fallbacks' => [ 'cursive' ],
			],
			[
				'name'      => 'Copperplate',
				'fallbacks' => [ 'Copperplate Gothic Light', 'fantasy' ],
			],
			[
				'name'      => 'Courier New',
				'fallbacks' => [ 'Courier', 'Lucida Sans Typewriter', 'Lucida Typewriter', 'monospace' ],
			],
			[
				'name'      => 'Century Gothic',
				'fallbacks' => [ 'CenturyGothic', 'AppleGothic', 'sans-serif' ],
			],
			[
				'name'      => 'Garamond',
				'fallbacks' => [ 'Baskerville', 'Baskerville Old Face', 'Hoefler Text', 'Times New Roman', 'serif' ],
			],
			[
				'name'      => 'Georgia',
				'fallbacks' => [ 'Times', 'Times New Roman', 'serif' ],
			],
			[
				'name'      => 'Gill Sans',
				'fallbacks' => [ 'Gill Sans MT', 'Calibri', 'sans-serif' ],
			],
			[
				'name'      => 'Lucida Bright',
				'fallbacks' => [ 'Georgia', 'serif' ],
			],
			[
				'name'      => 'Lucida Sans Typewriter',
				'fallbacks' => [ 'Lucida Console', 'monaco', 'Bitstream Vera Sans Mono', 'monospace' ],
			],
			[
				'name'      => 'Palatino',
				'fallbacks' => [ 'Palatino Linotype', 'Palatino LT STD', 'Book Antiqua', 'Georgia', 'serif' ],
			],
			[
				'name'      => 'Papyrus',
				'fallbacks' => [ 'fantasy' ],
			],
			[
				'name'      => 'Tahoma',
				'fallbacks' => [ 'Verdana', 'Segoe', 'sans-serif' ],
			],
			[
				'name'      => 'Times New Roman',
				'fallbacks' => [ 'Times New Roman', 'Times', 'Baskerville', 'Georgia', 'serif' ],
			],
			[
				'name'      => 'Trebuchet MS',
				'fallbacks' => [ 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', 'Tahoma', 'sans-serif' ],
			],
			[
				'name'      => 'Verdana',
				'fallbacks' => [ 'Geneva', 'sans-serif' ],
			],
		];
		$file  = __DIR__ . '/data/fonts.json';
		$fonts = array_merge( $fonts, self::get_google_fonts( $file ) );

		$columns = wp_list_pluck( $fonts, 'name' );
		array_multisort( $columns, SORT_ASC, $fonts );

		$fonts_url = 'https://fonts.googleapis.com/css';
		$subsets   = [ 'latin', 'latin-ext' ];

		/*
		 * Translators: To add an additional character subset specific to your language,
		 * translate this to 'greek', 'cyrillic', 'devanagari' or 'vietnamese'. Do not translate into your own language.
		 */
		$subset = _x( 'no-subset', 'Add new subset (greek, cyrillic, devanagari, vietnamese)', 'amp' );

		if ( 'cyrillic' === $subset ) {
			$subsets[] = 'cyrillic';
			$subsets[] = 'cyrillic-ext';
		} elseif ( 'greek' === $subset ) {
			$subsets[] = 'greek';
			$subsets[] = 'greek-ext';
		} elseif ( 'devanagari' === $subset ) {
			$subsets[] = 'devanagari';
		} elseif ( 'vietnamese' === $subset ) {
			$subsets[] = 'vietnamese';
		}

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
			$variants = array_intersect(
				$font['variants'],
				[
					'regular',
					'italic',
					'700',
					'700italic',
				]
			);

			$variants = array_map(
				static function ( $variant ) {
					$variant = str_replace(
						[ '0italic', 'regular', 'italic' ],
						[ '0i', '400', '400i' ],
						$variant
					);

					return $variant;
				},
				$variants
			);

			$gfont = '';

			if ( $variants ) {
				$gfont = $font['family'] . ':' . implode( ',', $variants );
			}

			$fonts[] = [
				'name'      => $font['family'],
				'fallbacks' => (array) self::get_font_fallback( $font['category'] ),
				'gfont'     => $gfont,
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
