<?php
/**
 * Create post to test all Gutenberg blocks.
 *
 * @codeCoverageIgnore
 * @package AMP
 */

/**
 * Gets many of the Gutenberg fixture blocks in /blocks/tests/fixtures.
 *
 * @throws Exception If this is script is not run inside the plugin directory.
 * @return string $content Post content with all Gutenberg blocks.
 */
function amp_get_blocks() {
	$fixtures_dir = dirname( dirname( __DIR__ ) ) . '/gutenberg/blocks/test/fixtures';
	$content      = '';
	if ( ! is_dir( $fixtures_dir ) ) {
		$fixtures_dir = dirname( $fixtures_dir );
		if ( ! is_dir( $fixtures_dir ) ) {
			throw new Exception( 'Please run this script from the AMP plugin root.' );
		}
	}

	foreach ( glob( $fixtures_dir . '/*.html' ) as $file ) {
		if ( ! preg_match( '/(serialized|embed|custom-text-teaser)/', $file ) ) {
			// Add the block's title.
			if ( preg_match( ':core__(<block>.+)\.html:s', basename( $file ), $matches ) ) {
				$content .= sprintf( '<h1>%s</h1>', $matches['block'] );
			}
			$content .= file_get_contents( $file ); // @codingStandardsIgnoreLine: file_get_contents_file_get_contents and file_system_read_file_get_contents.
		}
	}
	return $content;
}

/**
 * Creates a Gutenberg test post (page).
 *
 * @throws Exception If there is an error in creating the test page.
 * @param string $content The content to add to the post.
 * @return int Page ID.
 */
function amp_create_gutenberg_test_post( $content ) {
	$slug            = 'amp-test-gutenberg-blocks';
	$page            = get_page_by_path( $slug );
	$failure_message = 'The test page could not be added, please try again.';
	if ( $page ) {
		$page_id = $page->ID;
	} else {
		$page_id = wp_insert_post( array(
			'post_name'  => $slug,
			'post_title' => 'Test Gutenberg Blocks',
			'post_type'  => 'page',
		) );

		if ( ! $page_id || is_wp_error( $page_id ) ) {
			throw new Exception( $failure_message );
		}
	}

	$update = wp_update_post( array(
		'ID'           => $page_id,
		'post_content' => $content,
	) );

	if ( ! $update ) {
		throw new Exception( $failure_message );
	}
	return $update;
}

// Bootstrap.
if ( defined( 'WP_CLI' ) ) {
	try {
		$post_id = amp_create_gutenberg_test_post( amp_get_blocks() );
		WP_CLI::success( sprintf( 'The test page is at: %s', amp_get_permalink( $post_id ) . '#development=1' ) );
	} catch ( Exception $e ) {
		WP_CLI::error( $e->getMessage() );
	}
} else {
	echo "This script should be run WP-CLI via: wp eval-file bin/create-gutenberg-test-post.php\n";
	exit( 1 );
}
