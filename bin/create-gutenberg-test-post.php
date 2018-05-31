<?php
/**
 * Creates a post to test all Gutenberg blocks.
 *
 * @codeCoverageIgnore
 * @package AMP
 */

namespace AMP;

/**
 * Gets many of the Gutenberg fixture blocks in blocks/tests/fixtures/.
 *
 * @throws \Exception If this is script is not run inside the plugin directory, or if it doesn't have .html fixtures.
 * @return string $content Post content with all Gutenberg blocks.
 */
function get_test_block_fixtures() {
	$gutenberg_dir = dirname( dirname( __DIR__ ) ) . '/gutenberg';
	$content       = get_test_block_permutations();
	if ( ! is_dir( $gutenberg_dir ) ) {
		$gutenberg_dir = dirname( $gutenberg_dir );
		if ( ! is_dir( $gutenberg_dir ) ) {
			throw new \Exception( 'Please run this script from the AMP plugin root.' );
		}
	}

	$fixtures_dir = $gutenberg_dir . '/blocks/test/fixtures';
	if ( ! is_dir( $fixtures_dir ) ) {
		throw new \Exception( "Test files not found in the Gutenberg plugin. You may need to clone the plugin repo: \nhttps://github.com/WordPress/gutenberg.git" );
	}

	foreach ( glob( $fixtures_dir . '/*.html' ) as $file ) {
		if ( ! preg_match( '/(serialized|embed|shortcode|custom-text-teaser|core__block)/', $file ) ) {
			// Add the block's title.
			preg_match( ':core__(?P<block>.+)\.html:s', basename( $file ), $matches );
			if ( isset( $matches['block'] ) ) {
				$content .= sprintf( '<h1>%s</h1>', $matches['block'] );
			}
			$content .= file_get_contents( $file ); // @codingStandardsIgnoreLine: file_get_contents_file_get_contents, file_system_read_file_get_contents.
		}
	}

	// Replace broken URLs in fixture files.
	$content = str_replace( 'http://google.com/hi.png', 'https://cldup.com/-3VMmmrPm9.jpg', $content );
	$content = str_replace( 'https://awesome-fake.video/file.mp4', 'https://videos.files.wordpress.com/DK5mLrbr/video-ca6dc0ab4a_hd.mp4', $content );
	return $content;
}

/**
 * Gets the Gutenberg block permutations.
 *
 * These are mostly copied from gutenberg/blocks/test/fixtures/, and slightly modified.
 * Embeds and shortcodes are tested in a separate script, so this does not have have many.
 *
 * @return string $content The blocks as HTML.
 */
function get_test_block_permutations() {
	$blocks = array(
		array(
			'title'   => '(Reusable) Block With Video',
			'content' => sprintf( '<!-- wp:block {"ref":%d} /-->', create_test_reusable_block() ),
		),
		array(
			'title'   => 'Categories With Dropdown',
			'content' => '<!-- wp:core/categories {"showPostCounts":false,"displayAsDropdown":true,"showHierarchy":false} /-->',
		),
		array(
			'title'   => 'Columns, With 2 Columns',
			'content' => '<!-- wp:core/columns {"columns":2} --><div class="wp-block-columns has-2-columns"><!-- wp:core/paragraph {"layout":"column-1"} --><p class="layout-column-1">Column One, Paragraph One</p><!-- /wp:core/paragraph --><!-- wp:core/paragraph {"layout":"column-1"} --><p class="layout-column-1">Column One, Paragraph Two</p><!-- /wp:core/paragraph --><!-- wp:core/paragraph {"layout":"column-2"} --><p class="layout-column-2">Column Two, Paragraph One</p><!-- /wp:core/paragraph --></div><!-- /wp:core/columns -->',
		),
		array(
			'title'   => 'Cover Image With Fixed Background',
			'content' => '<!-- wp:core/cover-image {"url":"https://cldup.com/uuUqE_dXzy.jpg","dimRatio":40} --><section class="wp-block-cover-image has-background-dim-40 has-background-dim has-parallax" style="background-image:url(https://cldup.com/uuUqE_dXzy.jpg)"><h2>Guten Berg!</h2></section><!-- /wp:core/cover-image -->',
		),
		array(
			'title'   => 'WordPress Embed',
			'content' => '<!-- wp:core-embed/wordpress {"url":"https://make.wordpress.org/core/2017/12/11/whats-new-in-gutenberg-11th-december/"} --><figure class="wp-block-embed-wordpress wp-block-embed">https://make.wordpress.org/core/2017/12/11/whats-new-in-gutenberg-11th-december/<figcaption>Embedded content from WordPress</figcaption></figure><!-- /wp:core-embed/wordpress -->',
		),
		array(
			'title'   => 'YouTube Embed',
			'content' => '<!-- wp:core-embed/youtube {"url":"https://www.youtube.com/watch?v=GGS-tKTXw4Y"} --><figure class="wp-block-embed-youtube wp-block-embed">https://www.youtube.com/watch?v=GGS-tKTXw4Y<figcaption>Embedded content from youtube</figcaption></figure><!-- /wp:core-embed/youtube -->',
		),
		array(
			'title'   => 'Twitter Embed',
			'content' => '<!-- wp:core-embed/twitter {"url":"https://twitter.com/AMPhtml/status/963443140005957632"} --><figure class="wp-block-embed-twitter wp-block-embed">https://twitter.com/AMPhtml/status/963443140005957632<figcaption>We are Automattic</figcaption></figure><!-- /wp:core-embed/twitter -->',
		),
		array(
			'title'   => 'Gallery With 3 Columns',
			'content' => '<!-- wp:core/gallery --><ul class="wp-block-gallery alignnone columns-3 is-cropped"><li class="blocks-gallery-item"><figure><img src="https://cldup.com/uuUqE_dXzy.jpg" alt="title" /></figure></li><li class="blocks-gallery-item"><figure><img src="https://cldup.com/-3VMmmrPm9.jpg" alt="title" /></figure></li><li class="blocks-gallery-item"><figure><img src="https://cldup.com/aMbxBM0zAi.jpg" alt="title" /></figure></li></ul><!-- /wp:core/gallery -->',
		),
		array(
			'title'   => 'Audio Shortcode',
			'content' => '<!-- wp:core/shortcode -->[audio src=https://wptavern.com/wp-content/uploads/2017/11/EPISODE-296-Gutenberg-Telemetry-Calypso-and-More-With-Matt-Mullenweg.mp3]<!-- /wp:core/shortcode -->',
		),
		array(
			'title'   => 'Caption Shortcode',
			'content' => '<!-- wp:core/shortcode -->[caption width=150]This is a caption[/caption]<!-- /wp:core/shortcode -->',
		),
	);

	$content = '';
	foreach ( $blocks as $block ) {
		$content .= sprintf( '<h2>%s</h2>', $block['title'] );
		$content .= $block['content'];
	}

	return $content;
}

/**
 * Creates a reusable block with a video.
 *
 * Reusable blocks are stored in custom post types.
 * This creates one, and returns the ID on success.
 *
 * @return int|\WP_Error $post_id The post ID where the reusable block is stored, and 0 or WP_Error in case of failure.
 */
function create_test_reusable_block() {
	return wp_insert_post( array(
		'post_type'    => 'wp_block',
		'post_title'   => 'Test Reusable Block',
		'post_content' => '<!-- wp:core/video --><figure class="wp-block-video"><video src="https://videos.files.wordpress.com/DK5mLrbr/video-ca6dc0ab4a_hd.mp4" controls=""></video></figure><!-- /wp:core/video -->',
		'post_status'  => 'publish',
	) );
}

/**
 * Creates a Gutenberg test post (page).
 *
 * @throws \Exception If there is an error in creating the test page.
 * @param string $content The content to add to the post.
 * @return int Page ID.
 */
function create_gutenberg_test_post( $content ) {
	$slug            = 'amp-test-gutenberg-blocks';
	$title           = 'AMP Test Gutenberg Blocks';
	$page            = get_page_by_path( "/{$slug}/" );
	$failure_message = 'The test page could not be added, please try again.';
	if ( $page ) {
		$page_id = $page->ID;
	} else {
		$page_id = wp_insert_post( array(
			'post_name'  => $slug,
			'post_title' => $title,
			'post_type'  => 'page',
		) );

		if ( ! $page_id || is_wp_error( $page_id ) ) {
			throw new \Exception( $failure_message );
		}
	}

	$update = wp_update_post( array(
		'ID'           => $page_id,
		'post_content' => $content,
	) );

	if ( ! $update ) {
		throw new \Exception( $failure_message );
	}
	return $update;
}

// Bootstrap.
if ( defined( 'WP_CLI' ) ) {
	try {
		$post_id = create_gutenberg_test_post( get_test_block_fixtures() );
		\WP_CLI::success( sprintf( 'The test page is at: %s', \amp_get_permalink( $post_id ) . '#development=1' ) );
	} catch ( \Exception $e ) {
		\WP_CLI::error( $e->getMessage() );
	}
} else {
	echo "This script should be run WP-CLI via: wp eval-file bin/create-gutenberg-test-post.php\n";
	exit( 1 );
}
