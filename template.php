<!doctype html>
<html amp>
<head>
	<title><?php echo esc_html( $amp_post->get_title() ); ?></title>
	<meta charset="utf-8">
	<link rel="canonical" href="<?php echo esc_url( $amp_post->get_canonical_url() ); ?>" />
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no,minimal-ui">
	<link href='https://fonts.googleapis.com/css?family=Merriweather' rel='stylesheet' type='text/css'>
	<?php foreach ( $amp_post->get_scripts() as $element => $script ) : ?>
		<script element="<?php echo esc_attr( $element ); ?>" src="<?php echo esc_url( $script ); ?>" async></script>
	<?php endforeach; ?>	
	<script src="https://cdn.ampproject.org/v0.js" async development></script>
	<script type="application/ld+json"><?php echo json_encode( $amp_post->get_metadata() ); ?></script>
	<?php do_action( 'amp_head', $amp_post ); ?>
	<style>body {opacity: 0}</style><noscript><style>body {opacity: 1}</style></noscript>
	<style amp-custom>
	/* Generic WP styling */
	amp-img.alignright { float: right; margin: 0 0 1em 1em; }
	amp-img.alignleft { float: left; margin: 0 1em 1em 0; }
	amp-img.aligncenter { display: block; margin-left: auto; margin-right: auto; }
	.alignright { float: right; }
	.alignleft { float: left; }
	.aligncenter { display: block; margin-left: auto; margin-right: auto; }
	/* WP.com-specific styling */
	body {
		font-family: 'Merriweather', Serif;
		font-size: 16px;
		line-height: 1.8;
	}
	.content {
		max-width: 700px;
		margin: 0 auto;
		padding: 16px;
		overflow-wrap: break-word;
		word-wrap: break-word;
		font-weight: 400;
		color: #3d596d;
	}
	.title {
		margin: 0 0 16px 0;
		font-size: 36px;
		line-height: 1.258;
		font-weight: 700;
		color: #2e4453;
	}
	.meta {
		margin-bottom: 16px;
	}
	p {
		margin: 0 0 24px 0;
	}
	a,
	a:visited {
		color: #0087be;
	}
	a:hover,
	a:active,
	a:focus {
		color: #33bbe3;
	}
	.byline amp-img {
		border-radius: 50%;
		border: 0;
		background: #f3f6f8;
		position: relative;
		top: 6px;
		margin: 1px 6px 1px 0;
	}
	</style>
</head>
<body>
<div class="content">
	<h1 class="title"><?php echo esc_html( $amp_post->get_title() ); ?></h1>
	<div class="meta">
		<span class="byline">
			<?php echo $amp_post->get_author_avatar(); ?>
			<span class="author"><?php echo $amp_post->get_author_name(); ?></span>
		</span>
		&bull;
		<time datetime="<?php echo esc_attr( $amp_post->get_machine_date() ); ?>"><?php echo esc_html( $amp_post->get_human_date() ); ?></time>
	</div>
	<?php echo $amp_post->get_content(); ?>
</div>
<!-- <?php printf( 'Generated in %ss', timer_stop() ); ?> -->
</body>
</html>
