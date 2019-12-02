<?php
/**
 * The story editor.
 *
 * @package AMP
 */

// don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

require_once ABSPATH . 'wp-admin/admin-header.php';

AMP_Story_Post_Type::enqueue_block_editor_scripts();

global $post, $post_type;

$config = [];

$config['api'] = [
	'stories' => sprintf( '/wp/v2/%s', $post_type ),
	'media'   => '/wp/v2/media',
];

if ( ! empty( $post ) ) {
	$config['storyId'] = $post->ID;
}

?>

<div id="edit-story" data-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>">
	<h1>Please wait...</h1>
</div>
