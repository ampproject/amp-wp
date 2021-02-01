<?php
/**
 * Trait ThemesApiRequestMocking.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Helpers;

/**
 * Helper trait for to mock HTTP requests to the WordPress.org themes API.
 *
 * @package AmpProject\AmpWP
 */
trait ThemesApiRequestMocking {

	/**
	 * Reader themes from wordpress.org.
	 */
	protected static $api_reader_themes = [
		[
			'name'           => 'Twenty Twenty',
			'slug'           => 'twentytwenty',
			'version'        => '1.3',
			'preview_url'    => 'https://wp-themes.com/twentytwenty',
			'author'         =>
				[
					'user_nicename' => 'wordpressdotorg',
					'profile'       => 'https://profiles.wordpress.org/wordpressdotorg',
					'avatar'        => 'https://secure.gravatar.com/avatar/61ee2579b8905e62b4b4045bdc92c11a?s=96&d=monsterid&r=g',
					'display_name'  => 'WordPress.org',
				],
			'screenshot_url' => '//ts.w.org/wp-content/themes/twentytwenty/screenshot.png?ver=1.3',
			'rating'         => 86,
			'num_ratings'    => '37',
			'homepage'       => 'https://wordpress.org/themes/twentytwenty/',
			'description'    => 'Our default theme for 2020 is designed to take full advantage of the flexibility of the block editor. Organizations and businesses have the ability to create dynamic landing pages with endless layouts using the group and column blocks. The centered content column and fine-tuned typography also makes it perfect for traditional blogs. Complete editor styles give you a good idea of what your content will look like, even before you publish. You can give your site a personal touch by changing the background colors and the accent color in the Customizer. The colors of all elements on your site are automatically calculated based on the colors you pick, ensuring a high, accessible color contrast for your visitors.',
			'requires'       => '4.7',
			'requires_php'   => '5.2.4',
		],
		[
			'name'           => 'Twenty Nineteen',
			'slug'           => 'twentynineteen',
			'version'        => '1.5',
			'preview_url'    => 'https://wp-themes.com/twentynineteen',
			'author'         =>
				[
					'user_nicename' => 'wordpressdotorg',
					'profile'       => 'https://profiles.wordpress.org/wordpressdotorg',
					'avatar'        => 'https://secure.gravatar.com/avatar/61ee2579b8905e62b4b4045bdc92c11a?s=96&d=monsterid&r=g',
					'display_name'  => 'WordPress.org',
				],
			'screenshot_url' => '//ts.w.org/wp-content/themes/twentynineteen/screenshot.png?ver=1.5',
			'rating'         => 72,
			'num_ratings'    => '54',
			'homepage'       => 'https://wordpress.org/themes/twentynineteen/',
			'description'    => 'Our 2019 default theme is designed to show off the power of the block editor. It features custom styles for all the default blocks, and is built so that what you see in the editor looks like what you\'ll see on your website. Twenty Nineteen is designed to be adaptable to a wide range of websites, whether you’re running a photo blog, launching a new business, or supporting a non-profit. Featuring ample whitespace and modern sans-serif headlines paired with classic serif body text, it\'s built to be beautiful on all screen sizes.',
			'requires'       => '4.9.6',
			'requires_php'   => '5.2.4',
		],
		[
			'name'           => 'Twenty Seventeen',
			'slug'           => 'twentyseventeen',
			'version'        => '2.3',
			'preview_url'    => 'https://wp-themes.com/twentyseventeen',
			'author'         =>
				[
					'user_nicename' => 'wordpressdotorg',
					'profile'       => 'https://profiles.wordpress.org/wordpressdotorg',
					'avatar'        => 'https://secure.gravatar.com/avatar/61ee2579b8905e62b4b4045bdc92c11a?s=96&d=monsterid&r=g',
					'display_name'  => 'WordPress.org',
				],
			'screenshot_url' => '//ts.w.org/wp-content/themes/twentyseventeen/screenshot.png?ver=2.3',
			'rating'         => 90,
			'num_ratings'    => '110',
			'homepage'       => 'https://wordpress.org/themes/twentyseventeen/',
			'description'    => 'Twenty Seventeen brings your site to life with header video and immersive featured images. With a focus on business sites, it features multiple sections on the front page as well as widgets, navigation and social menus, a logo, and more. Personalize its asymmetrical grid with a custom color scheme and showcase your multimedia content with post formats. Our default theme for 2017 works great in many languages, for any abilities, and on any device.',
			'requires'       => '4.7',
			'requires_php'   => '5.2.4',
		],
		[
			'name'           => 'Twenty Sixteen',
			'slug'           => 'twentysixteen',
			'version'        => '2.1',
			'preview_url'    => 'https://wp-themes.com/twentysixteen',
			'author'         =>
				[
					'user_nicename' => 'wordpressdotorg',
					'profile'       => 'https://profiles.wordpress.org/wordpressdotorg',
					'avatar'        => 'https://secure.gravatar.com/avatar/61ee2579b8905e62b4b4045bdc92c11a?s=96&d=monsterid&r=g',
					'display_name'  => 'WordPress.org',
				],
			'screenshot_url' => '//ts.w.org/wp-content/themes/twentysixteen/screenshot.png?ver=2.1',
			'rating'         => 82,
			'num_ratings'    => '76',
			'homepage'       => 'https://wordpress.org/themes/twentysixteen/',
			'description'    => 'Twenty Sixteen is a modernized take on an ever-popular WordPress layout — the horizontal masthead with an optional right sidebar that works perfectly for blogs and websites. It has custom color options with beautiful default color schemes, a harmonious fluid grid using a mobile-first approach, and impeccable polish in every detail. Twenty Sixteen will make your WordPress look beautiful everywhere.',
			'requires'       => '4.4',
			'requires_php'   => '5.2.4',
		],
		[
			'name'           => 'Twenty Fifteen',
			'slug'           => 'twentyfifteen',
			'version'        => '2.6',
			'preview_url'    => 'https://wp-themes.com/twentyfifteen',
			'author'         =>
				[
					'user_nicename' => 'wordpressdotorg',
					'profile'       => 'https://profiles.wordpress.org/wordpressdotorg',
					'avatar'        => 'https://secure.gravatar.com/avatar/61ee2579b8905e62b4b4045bdc92c11a?s=96&d=monsterid&r=g',
					'display_name'  => 'WordPress.org',
				],
			'screenshot_url' => '//ts.w.org/wp-content/themes/twentyfifteen/screenshot.png?ver=2.6',
			'rating'         => 88,
			'num_ratings'    => '48',
			'homepage'       => 'https://wordpress.org/themes/twentyfifteen/',
			'description'    => 'Our 2015 default theme is clean, blog-focused, and designed for clarity. Twenty Fifteen\'s simple, straightforward typography is readable on a wide variety of screen sizes, and suitable for multiple languages. We designed it using a mobile-first approach, meaning your content takes center-stage, regardless of whether your visitors arrive by smartphone, tablet, laptop, or desktop computer.',
			'requires'       => false,
			'requires_php'   => '5.2.4',
		],
		[
			'name'           => 'Twenty Fourteen',
			'slug'           => 'twentyfourteen',
			'version'        => '2.8',
			'preview_url'    => 'https://wp-themes.com/twentyfourteen',
			'author'         =>
				[
					'user_nicename' => 'wordpressdotorg',
					'profile'       => 'https://profiles.wordpress.org/wordpressdotorg',
					'avatar'        => 'https://secure.gravatar.com/avatar/61ee2579b8905e62b4b4045bdc92c11a?s=96&d=monsterid&r=g',
					'display_name'  => 'WordPress.org',
				],
			'screenshot_url' => '//ts.w.org/wp-content/themes/twentyfourteen/screenshot.png?ver=2.8',
			'rating'         => 88,
			'num_ratings'    => '93',
			'homepage'       => 'https://wordpress.org/themes/twentyfourteen/',
			'description'    => 'In 2014, our default theme lets you create a responsive magazine website with a sleek, modern design. Feature your favorite homepage content in either a grid or a slider. Use the three widget areas to customize your website, and change your content\'s layout with a full-width page template and a contributor page to show off your authors. Creating a magazine website with WordPress has never been easier.',
			'requires'       => false,
			'requires_php'   => '5.2.4',
		],
		[
			'name'           => 'Twenty Thirteen',
			'slug'           => 'twentythirteen',
			'version'        => '3.0',
			'preview_url'    => 'https://wp-themes.com/twentythirteen',
			'author'         =>
				[
					'user_nicename' => 'wordpressdotorg',
					'profile'       => 'https://profiles.wordpress.org/wordpressdotorg',
					'avatar'        => 'https://secure.gravatar.com/avatar/61ee2579b8905e62b4b4045bdc92c11a?s=96&d=monsterid&r=g',
					'display_name'  => 'WordPress.org',
				],
			'screenshot_url' => '//ts.w.org/wp-content/themes/twentythirteen/screenshot.png?ver=3.0',
			'rating'         => 82,
			'num_ratings'    => '62',
			'homepage'       => 'https://wordpress.org/themes/twentythirteen/',
			'description'    => 'The 2013 theme for WordPress takes us back to the blog, featuring a full range of post formats, each displayed beautifully in their own unique way. Design details abound, starting with a vibrant color scheme and matching header images, beautiful typography and icons, and a flexible layout that looks great on any device, big or small.',
			'requires'       => '3.6',
			'requires_php'   => '5.2.4',
		],
		[
			'name'           => 'Twenty Twelve',
			'slug'           => 'twentytwelve',
			'version'        => '3.1',
			'preview_url'    => 'https://wp-themes.com/twentytwelve',
			'author'         =>
				[
					'user_nicename' => 'wordpressdotorg',
					'profile'       => 'https://profiles.wordpress.org/wordpressdotorg',
					'avatar'        => 'https://secure.gravatar.com/avatar/61ee2579b8905e62b4b4045bdc92c11a?s=96&d=monsterid&r=g',
					'display_name'  => 'WordPress.org',
				],
			'screenshot_url' => '//ts.w.org/wp-content/themes/twentytwelve/screenshot.png?ver=3.1',
			'rating'         => 92,
			'num_ratings'    => '155',
			'homepage'       => 'https://wordpress.org/themes/twentytwelve/',
			'description'    => 'The 2012 theme for WordPress is a fully responsive theme that looks great on any device. Features include a front page template with its own widgets, an optional display font, styling for post formats on both index and single views, and an optional no-sidebar page template. Make it yours with a custom menu, header image, and background.',
			'requires'       => '3.5',
			'requires_php'   => '5.2.4',
		],
		[
			'name'           => 'Twenty Eleven',
			'slug'           => 'twentyeleven',
			'version'        => '3.4',
			'preview_url'    => 'https://wp-themes.com/twentyeleven',
			'author'         =>
				[
					'user_nicename' => 'wordpressdotorg',
					'profile'       => 'https://profiles.wordpress.org/wordpressdotorg',
					'avatar'        => 'https://secure.gravatar.com/avatar/61ee2579b8905e62b4b4045bdc92c11a?s=96&d=monsterid&r=g',
					'display_name'  => 'WordPress.org',
				],
			'screenshot_url' => '//ts.w.org/wp-content/themes/twentyeleven/screenshot.png?ver=3.4',
			'rating'         => 94,
			'num_ratings'    => '45',
			'homepage'       => 'https://wordpress.org/themes/twentyeleven/',
			'description'    => 'The 2011 theme for WordPress is sophisticated, lightweight, and adaptable. Make it yours with a custom menu, header image, and background -- then go further with available theme options for light or dark color scheme, custom link colors, and three layout choices. Twenty Eleven comes equipped with a Showcase page template that transforms your front page into a showcase to show off your best content, widget support galore (sidebar, three footer areas, and a Showcase page widget area), and a custom "Ephemera" widget to display your Aside, Link, Quote, or Status posts. Included are styles for print and for the admin editor, support for featured images (as custom header images on posts and pages and as large images on featured "sticky" posts), and special styles for six different post formats.',
			'requires'       => false,
			'requires_php'   => '5.2.4',
		],
	];

	/**
	 * Filters the external request within themes_api while testing.
	 */
	public function add_reader_themes_request_filter() {
		add_filter(
			'pre_http_request',
			static function ( $pre, $r, $request_url ) {
				if ( in_array( 'external-http', $_SERVER['argv'], true ) ) {
					return $pre;
				}

				if ( 0 !== strpos( $request_url, 'https://api.wordpress.org/themes/' ) || false === strpos( $request_url, 'wordpressdotorg' ) ) {
					return $pre;
				}

				return [
					'body'     => wp_json_encode( [ 'themes' => self::$api_reader_themes ] ),
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
				];
			},
			10,
			3
		);
	}
}
