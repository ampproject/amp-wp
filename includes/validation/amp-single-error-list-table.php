<?php
/**
 * Template for the single error list table.
 *
 * Mainly copied from edit-tags.php, and needs much more code.
 * This is output on the post.php page for amp_invalid_url,
 * where the editor normally would be.
 * It outputs a WP_Terms_List_Table of amp_validation_error terms.
 *
 * @package AMP
 * @since 1.0
 */

global $post_type;

if ( ! isset( $_GET['taxonomy'] ) ) { // WPCS: CSRF OK.
	exit;
}

$taxonomy = sanitize_key( $_GET['taxonomy'] ); // WPCS: CSRF OK.
$tax      = get_taxonomy( $taxonomy );
if ( ! $tax ) {
	wp_die( esc_html__( 'Invalid taxonomy.', 'default' ) );
}

if ( ! in_array( $tax->name, get_taxonomies( array( 'show_ui' => true ) ), true ) ) {
	wp_die( esc_html__( 'Sorry, you are not allowed to edit terms in this taxonomy.', 'default' ) );
}

if ( ! current_user_can( $tax->cap->manage_terms ) ) {
	wp_die(
		'<h1>' . esc_html__( 'You need a higher level of permission.', 'default' ) . '</h1>' .
		'<p>' . esc_html__( 'Sorry, you are not allowed to manage terms in this taxonomy.', 'default' ) . '</p>',
		403
	);
}

$wp_list_table = _get_list_table( 'WP_Terms_List_Table' );
$pagenum       = $wp_list_table->get_pagenum();
$title         = $tax->labels->name;

get_current_screen()->set_screen_reader_content( array(
	'heading_pagination' => $tax->labels->items_list_navigation,
	'heading_list'       => $tax->labels->items_list,
) );

$wp_list_table->prepare_items();
$wp_list_table->views();

?>
<form id="posts-filter" method="post">
<input type="hidden" name="taxonomy" value="<?php echo esc_attr( $taxonomy ); ?>" />
<input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ); ?>" />
<?php
$wp_list_table->search_box( esc_html__( 'Search Errors', 'amp' ), 'invalid-url-search' );
$wp_list_table->display();
