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

require_once( ABSPATH . 'wp-admin/admin-header.php' );


AMP_Story_Post_Type::enqueue_block_editor_scripts();

?>

<div id="edit-story">
	<h1>Story editor is loading...</h1>
</div>
