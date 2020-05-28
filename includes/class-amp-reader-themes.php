<?php
/**
 * Fetches and formats data for AMP reader themes.
 *
 * @package AMP
 * @since 1.6.0
 */

/**
 * Class AMP_Reader_Themes.
 *
 * @since 1.6.0
 */
final class AMP_Reader_Themes {
	/**
	 * Formatted theme data.
	 *
	 * @since 1.6.0
	 *
	 * @var array
	 */
	private $themes;

	/**
	 * Retrieves all AMP plugin options specified in the endpoint schema.
	 *
	 * @since 1.6.0
	 *
	 * @return array Formatted theme data.
	 */
	public function get_themes() {
		if ( ! is_null( $this->themes ) ) {
			return $this->themes;
		}

		/**
		 * Filters supported reader themes.
		 *
		 * @param array Reader theme objects.
		 */
		$this->themes = apply_filters( 'amp_reader_themes', $this->get_theme_data() );

		$this->install_reader_theme( 'twentyeleven' );

		return $this->themes;
	}

	/**
	 * Installs a theme from the WP repo.
	 *
	 * @param string $slug Theme slug.
	 * @return bool|WP_Error True if the installation was successful, false or a WP_Error object otherwise.
	 */
	private function install_reader_theme( $slug ) {
		if ( ! function_exists( 'themes_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/theme.php';
		}

		if ( ! class_exists( 'Theme_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/misc.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once ABSPATH . 'wp-admin/includes/class-theme-upgrader.php';
		}

		$api = themes_api(
			'query_themes',
			[
				'author' => 'wordpressdotorg',
				'fields' => [
					
				]
			],
		);

		$upgrader = new Theme_Upgrader();

		return $upgrader->install( $api->download_link );
	}

	/**
	 * Retrieves theme data.
	 *
	 * @since 1.6.0
	 *
	 * @return array Theme ecosystem posts copied the amp-wp.org website.
	 */
	public function get_theme_data() {
		return [
			[
				'slug'    => 'classic',
				'title'   => 'AMP Classic',
				'content' => __( 'A legacy default template that looks nice and clean, with a good balance between ease and extensibility when it comes to customization.', 'amp' ),
				'media'   =>
				[
					'alt_text'      => 'AMP Classic Theme',
					'media_details' =>
					[
						'full' =>
						[
							'height'     => 679,
							'source_url' => '//via.placeholder.com/1024x679',
							'width'      => 1024,
						],
					],
				],
				'link'    => 'https://amp-wp.org',
			],
			[
				'slug'    => 'twentytwenty',
				'title'   => 'Twenty Twenty',
				'content' => __( 'Our default theme for 2020 is designed to take full advantage of the flexibility of the block editor. Organizations and businesses have the ability to create dynamic landing pages with endless layouts using the group and column blocks. The centered content column and fine-tuned typography also makes it perfect for traditional blogs. Complete editor styles give you a good idea of what your content will look like, even before you publish. You can give your site a personal touch by changing the background colors and the accent color in the Customizer. The colors of all elements on your site are automatically calculated based on the colors you pick, ensuring a high, accessible color contrast for your visitors.', 'amp' ),
				'media'   =>
				[
					'alt_text'      => 'Twenty Twenty',
					'media_details' =>
					[
						'full' =>
						[
							'height'     => 679,
							'source_url' => '//via.placeholder.com/1024x679',
							'width'      => 1024,
						],
					],
				],
				'link'    => 'https://wordpress.org/themes/twentytwenty/',
			],
			[
				'slug'    => 'twentynineteen',
				'title'   => 'Twenty Nineteen',
				'content' => __( 'Our 2019 default theme is designed to show off the power of the block editor. It features custom styles for all the default blocks, and is built so that what you see in the editor looks like what you&#8217;ll see on your website. Twenty Nineteen is designed to be adaptable to a wide range of websites, whether you’re running a photo blog, launching a new business, or supporting a non-profit. Featuring ample whitespace and modern sans-serif headlines paired with classic serif body text, it&#8217;s built to be beautiful on all screen sizes.', 'amp' ),
				'media'   =>
				[
					'title'         => 'cropped-twentynineteen.png',
					'alt_text'      => '',
					'media_details' =>
					[
						'full' =>
						[
							'height'     => 679,
							'source_url' => '//via.placeholder.com/1024x679',
							'width'      => 1024,
						],
					],
				],
				'link'    => 'https://wordpress.org/themes/twentynineteen/',
			],
			[
				'slug'    => 'twentyseventeen',
				'title'   => 'Twenty Seventeen',
				'content' => __( 'Twenty Seventeen brings your site to life with header video and immersive featured images. With a focus on business sites, it features multiple sections on the front page as well as widgets, navigation and social menus, a logo, and more. Personalize its asymmetrical grid with a custom color scheme and showcase your multimedia content with post formats. Our default theme for 2017 works great in many languages, for any abilities, and on any device.', 'amp' ),
				'media'   =>
				[
					'title'         => 'cropped-twentyseventeen.png',
					'alt_text'      => '',
					'media_details' =>
					[
						'full' =>
						[
							'height'     => 679,
							'source_url' => '//via.placeholder.com/1024x679',
							'width'      => 1024,
						],
					],
				],
				'link'    => 'https://wordpress.org/themes/twentyseventeen/',
			],
			[
				'slug'    => 'twentysixteen',
				'title'   => 'Twenty Sixteen',
				'content' => __( 'Twenty Sixteen is a modernized take on an ever-popular WordPress layout — the horizontal masthead with an optional right sidebar that works perfectly for blogs and websites. It has custom color options with beautiful default color schemes, a harmonious fluid grid using a mobile-first approach, and impeccable polish in every detail. Twenty Sixteen will make your WordPress look beautiful everywhere.', 'amp' ),
				'media'   =>
				[
					'alt_text'      => 'Twenty Sixteen',
					'media_details' =>
					[
						'full' =>
						[
							'height'     => 679,
							'source_url' => '//via.placeholder.com/1024x679',
							'width'      => 1024,
						],
					],
				],
				'link'    => 'https://wordpress.org/themes/twentysixteen/',
			],
			[
				'slug'    => 'twentyfifteen',
				'title'   => 'Twenty Fifteen',
				'content' => __( 'Our 2015 default theme is clean, blog-focused, and designed for clarity. Twenty Fifteen&#8217;s simple, straightforward typography is readable on a wide variety of screen sizes, and suitable for multiple languages. We designed it using a mobile-first approach, meaning your content takes center-stage, regardless of whether your visitors arrive by smartphone, tablet, laptop, or desktop computer.', 'amp' ),
				'media'   =>
				[
					'alt_text'      => 'Twenty Fifteen',
					'media_details' =>
					[
						'full' =>
						[
							'height'     => 679,
							'source_url' => '//via.placeholder.com/1024x679',
							'width'      => 1024,
						],
					],
				],
				'link'    => 'https://wordpress.org/themes/twentyfifteen/',
			],
			[
				'slug'    => 'twentyfourteen',
				'title'   => 'Twenty Fourteen',
				'content' => __( 'In 2014, our default theme lets you create a responsive magazine website with a sleek, modern design. Feature your favorite homepage content in either a grid or a slider. Use the three widget areas to customize your website, and change your content&#8217;s layout with a full-width page template and a contributor page to show off your authors. Creating a magazine website with WordPress has never been easier.', 'amp' ),
				'media'   =>
				[
					'alt_text'      => 'Twenty Fourteen',
					'media_details' =>
					[
						'full' =>
						[
							'height'     => 679,
							'source_url' => '//via.placeholder.com/1024x679',
							'width'      => 1024,
						],
					],
				],
				'link'    => 'https://wordpress.org/themes/twentyfourteen',
			],
			[
				'slug'    => 'twentythirteen',
				'title'   => 'Twenty Thirteen',
				'content' => __( 'The 2013 theme for WordPress takes us back to the blog, featuring a full range of post formats, each displayed beautifully in their own unique way. Design details abound, starting with a vibrant color scheme and matching header images, beautiful typography and icons, and a flexible layout that looks great on any device, big or small.', 'amp' ),
				'media'   =>
				[
					'alt_text'      => 'Thirty Thirteen',
					'media_details' =>
					[
						'full' =>
						[
							'height'     => 679,
							'source_url' => '//via.placeholder.com/1024x679',
							'width'      => 1024,
						],
					],
				],
				'link'    => 'https://wordpress.org/themes/twentythirteen/',
			],
			[
				'slug'    => 'twentytwelve',
				'title'   => 'Twenty Twelve',
				'content' => __( 'The 2012 theme for WordPress is a fully responsive theme that looks great on any device. Features include a front page template with its own widgets, an optional display font, styling for post formats on both index and single views, and an optional no-sidebar page template. Make it yours with a custom menu, header image, and background.', 'amp' ),
				'media'   =>
				[
					'alt_text'      => 'Twenty Twelve',
					'media_details' =>
					[
						'full' =>
						[
							'height'     => 679,
							'source_url' => '//via.placeholder.com/1024x679',
							'width'      => 1024,
						],
					],
				],
				'link'    => 'https://wordpress.org/themes/twentytwelve/',
			],
			[
				'slug'    => 'twentyeleven',
				'title'   => 'Twenty Eleven',
				'content' => __( 'The 2011 theme for WordPress is sophisticated, lightweight, and adaptable. Make it yours with a custom menu, header image, and background &#8212; then go further with available theme options for light or dark color scheme, custom link colors, and three layout choices. Twenty Eleven comes equipped with a Showcase page template that transforms your front page into a showcase to show off your best content, widget support galore (sidebar, three footer areas, and a Showcase page widget area), and a custom &#8220;Ephemera&#8221; widget to display your Aside, Link, Quote, or Status posts. Included are styles for print and for the admin editor, support for featured images (as custom header images on posts and pages and as large images on featured &#8220;sticky&#8221; posts), and special styles for six different post formats.', 'amp' ),
				'media'   =>
				[
					'alt_text'      => 'Twenty Eleven',
					'media_details' =>
					[
						'full' =>
						[
							'height'     => 679,
							'source_url' => '//via.placeholder.com/1024x679',
							'width'      => 1024,
						],
					],
				],
				'link'    => 'https://wordpress.org/themes/twentyeleven/',
			],
		];
	}

	/**
	 * Prepares a single theme.
	 *
	 * @since 1.6.0
	 *
	 * @param array $item Post data from the remote REST endpoint.
	 * @return array Prepared theme object.
	 */
	public function prepare_theme( $item ) {
		$prepared_item = [];

		foreach ( $item as $key => $value ) {
			switch ( $key ) {
				case 'content':
				case 'title':
					$prepared_item[ $key ] = wp_strip_all_tags( $value['rendered'], true );
					break;

				case 'meta':
					$prepared_item['link'] = $value['ampps_link'];
					break;

				default:
					$prepared_item[ $key ] = $value;
			}
		}

		return $prepared_item;
	}

	/**
	 * Prepares featured media data.
	 *
	 * @since 1.6.0
	 *
	 * @param array $item Media details.
	 * @return array Prepared media details.
	 */
	public function prepare_media( $item ) {
		$prepared_item = [];

		foreach ( $item as $key => $value ) {
			switch ( $key ) {
				case 'title':
					$prepared_item[ $key ] = wp_strip_all_tags( $value['rendered'], true );
					break;

				case 'media_details':
					$prepared_item[ $key ] = [
						'full'  => empty( $item[ $key ]['sizes']['full'] )
							? null
							: [
								'height'     => $item[ $key ]['sizes']['full']['height'],
								'source_url' => $item[ $key ]['sizes']['full']['source_url'],
								'width'      => $item[ $key ]['sizes']['full']['width'],
							],
						'large' => empty( $item[ $key ]['sizes']['large'] )
							? null
							: [
								'height'     => $item[ $key ]['sizes']['large']['height'],
								'source_url' => $item[ $key ]['sizes']['large']['source_url'],
								'width'      => $item[ $key ]['sizes']['large']['width'],
							],
					];
					break;

				default:
					$prepared_item[ $key ] = $value;
			}
		}

		return $prepared_item;
	}
}
