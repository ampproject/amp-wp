<?php
/**
 * Create embed test post.
 *
 * @package AMP
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "Must be run in WP-CLI via: wp eval-file bin/create-embed-test-post.php\n";
	exit( 1 );
}

$data_entries = array(
	array(
		'prepare' => 'amp_test_prepare_image_attachments',
		'heading' => 'Media Gallery',
		'content' => function( $data ) {
			return sprintf( '[gallery ids="%s"]', implode( ',', $data['ids'] ) );
		},
	),
);

/**
 * Prepare test by ensuring attachments exist.
 *
 * @param array $data Entry data.
 * @return array Data.
 */
function amp_test_prepare_image_attachments( $data ) {
	$attachments = get_children( array(
		'post_parent' => 0,
		'post_status' => 'inherit',
		'post_type' => 'attachment',
		'post_mime_type' => 'image',
	) );
	$data['ids'] = wp_list_pluck( $attachments, 'ID' );

	// @todo Add some attachments if count( $data['ids'] ) < 5.
	return $data;
}

// Run the script.
$page = get_page_by_path( '/amp-test-embeds/' );
if ( $page ) {
	$page_id = $page->ID;
} else {
	$page_id = wp_insert_post( array(
		'post_name' => 'amp-test-embeds',
		'post_title' => 'AMP Test Embeds',
		'post_type' => 'page',
	) );
}

$content = '';
foreach ( $data_entries as $data_entry ) {
	if ( isset( $data_entry['prepare'] ) ) {
		$data_entry = array_merge(
			$data_entry,
			call_user_func( $data_entry['prepare'], $data_entry )
		);
	}

	$content .= sprintf( "<h1>%s</h1>\n", $data_entry['heading'] );
	if ( is_callable( $data_entry['content'] ) ) {
		$content .= call_user_func( $data_entry['content'], $data_entry );
	} else {
		$content .= $data_entry['content'];
	}
	$content .= "\n\n";
}

wp_update_post( wp_slash( array(
	'ID' => $page_id,
	'post_content' => $content,
) ) );

WP_CLI::success( sprintf( 'Please take a look at: %s', get_permalink( $page_id ) ) );
