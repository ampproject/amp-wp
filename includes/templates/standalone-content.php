<?php
/**
 * AMP standalone content template.
 *
 * This is a minimally-valid AMP document which is intended to be used with the AMP Shadow Doc API to embed the post
 * content in isolation.
 *
 * @link https://github.com/ampproject/amphtml/blob/master/spec/amp-shadow-doc.md
 *
 * @package AMP
 */

the_post();
?>
<!DOCTYPE html>
<html amp>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width">
		<?php echo amp_get_boilerplate_code(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php
		wp_enqueue_scripts();
		wp_scripts()->do_items( [ 'amp-runtime' ] ); // @todo Duplicate with AMP_Theme_Support::enqueue_assets().
		wp_styles()->do_items();
		?>
	</head>
	<body>
		<?php the_content(); ?>
	</body>
</html>
