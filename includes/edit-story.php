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


?>

<div id="edit-story">
	<h1><?php esc_html_e( 'Please wait...', 'amp' ); ?></h1>
</div>
