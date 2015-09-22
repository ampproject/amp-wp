<!doctype html>
<html amp>
<head>
	<title><?php echo esc_html( $amp_post->get_title() ); ?></title>
	<meta charset="utf-8">
	<link rel="canonical" href="<?php echo esc_url( $amp_post->get_canonical_url() ); ?>" />
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no,minimal-ui">
	<?php foreach ( $amp_post->get_scripts() as $element => $script ) : ?>
		<script element="<?php echo esc_attr( $element ); ?>" src="<?php echo esc_url( $script ); ?>" async></script>
	<?php endforeach; ?>	
	<script src="https://www.gstatic.com/amphtml/v0.js"></script>
	<script type="application/ld+json"><?php echo json_encode( $amp_post->get_metadata() ); ?></script>
	<?php do_action( 'amp_head', $amp_post ); ?>
	<style>body {opacity: 0}</style><noscript><style>body {opacity: 1}</style></noscript>
</head>
<body>
<h1><?php echo esc_html( $amp_post->get_title() ); ?></h1>
<time datetime="<?php echo esc_attr( $amp_post->get_machine_date() ); ?>"><?php echo esc_html( $amp_post->get_human_date() ); ?></time>
<?php echo $amp_post->get_content(); ?>
<!-- <?php printf( 'Generated in %ss', timer_stop() ); ?> -->
</body>
</html>
