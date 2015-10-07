<!doctype html>
<html amp>
<head>
	<title><?php echo esc_html( $amp_post->get_title() ); ?> | <?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
	<meta charset="utf-8">
	<link rel="canonical" href="<?php echo esc_url( $amp_post->get_canonical_url() ); ?>" />
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no,minimal-ui">
	<link href='https://fonts.googleapis.com/css?family=Merriweather|Open+Sans:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
	<?php foreach ( $amp_post->get_scripts() as $element => $script ) : ?>
		<script element="<?php echo esc_attr( $element ); ?>" src="<?php echo esc_url( $script ); ?>" async></script>
	<?php endforeach; ?>
	<script src="https://cdn.ampproject.org/v0.js" async <?php echo defined( 'AMP_DEV_MODE' ) && AMP_DEV_MODE ? 'development' : ''; ?>></script>
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


	/* Generic WP.com reader style */
	.content, .title-bar div {
		max-width: 600px;
		margin: 0 auto;
	}

	body {
		font-family: 'Merriweather', Serif;
		font-size: 16px;
		line-height: 1.8;
		background: #fff;
		color: #3d596d;
		padding-bottom: 100px;
	}

	.content {
		padding: 16px;
		overflow-wrap: break-word;
		word-wrap: break-word;
		font-weight: 400;
		color: #3d596d;
	}

	.title {
		margin: 36px 0 0 0;
		font-size: 36px;
		line-height: 1.258;
		font-weight: 700;
		color: #2e4453;
	}

	.meta {
		margin-bottom: 16px;
	}

	p,
	ol,
	ul,
	figure {
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


	/* Open Sans */
	.meta,
	nav.title-bar,
	.wp-caption-text {
		font-family: "Open Sans", sans-serif;
		font-size: 15px;
	}


	/* Meta */
	ul.meta {
		padding: 24px 0 0 0;
		margin: 0 0 24px 0;
	}

	ul.meta li {
		list-style: none;
		display: inline-block;
		margin: 0 8px 0 0;
		line-height: 24px;
	    white-space: nowrap;
	    overflow: hidden;
	    text-overflow: ellipsis;
	    max-width: 300px;
	}

	.meta,
	.meta a {
		color: #4f748e;
	}

	.byline amp-img {
		border-radius: 50%;
		border: 0;
		background: #f3f6f8;
		position: relative;
		top: 6px;
		margin-right: 6px;
    }


	/* Titlebar */
	nav.title-bar {
		background: #0a89c0;
		padding: 0 16px;
	}

	nav.title-bar div {
		line-height: 46px;
		color: #fff;
	}

	nav.title-bar svg {
		fill: #fff;
		float: left;
		margin: 11px 8px 0 0;
	}


	/* Captions */
	.wp-caption-text {
		background: #eaf0f3;
		padding: 8px 16px;
	}


	/* Quotes */
	blockquote {
		padding: 16px;
		margin: 8px 0 24px 0;
		border-left: 2px solid #87a6bc;
		color: #4f748e;
		background: #e9eff3;
	}

	blockquote p:last-child {
		margin-bottom: 0;
	}
	</style>
</head>
<body>
<nav class="title-bar">
	<div><svg x="0px" y="0px" width="24" height="24" viewBox="0 0 24 24">
		<path class="st0" d="M12,0C5.4,0,0,5.4,0,12c0,6.6,5.4,12,12,12c6.6,0,12-5.4,12-12C24,5.4,18.6,0,12,0z M1.2,12
			c0-1.6,0.3-3,0.9-4.4l5.1,14.1C3.7,20,1.2,16.3,1.2,12z M12,22.8c-1.1,0-2.1-0.2-3-0.4l3.2-9.4l3.3,9.1c0,0.1,0,0.1,0.1,0.1
			C14.5,22.6,13.3,22.8,12,22.8z M13.5,6.9c0.6,0,1.2-0.1,1.2-0.1c0.6-0.1,0.5-0.9-0.1-0.9c0,0-1.7,0.1-2.9,0.1
			c-1.1,0-2.8-0.1-2.8-0.1c-0.6,0-0.7,0.9-0.1,0.9c0,0,0.6,0.1,1.1,0.1l1.7,4.6l-2.4,7.1L5.4,6.9c0.7,0,1.2-0.1,1.2-0.1
			c0.6-0.1,0.5-0.9-0.1-0.9c0,0-1.7,0.1-2.9,0.1c-0.2,0-0.4,0-0.7,0c1.9-2.9,5.2-4.9,9-4.9c2.8,0,5.4,1.1,7.3,2.8c0,0-0.1,0-0.1,0
			c-1.1,0-1.8,0.9-1.8,1.9c0,0.9,0.5,1.6,1.1,2.5c0.4,0.7,0.9,1.6,0.9,3c0,0.9-0.3,2.1-0.8,3.5l-1.1,3.6L13.5,6.9z M17.4,21.3
			l3.3-9.5c0.6-1.5,0.8-2.8,0.8-3.9c0-0.4,0-0.8-0.1-1.1c0.8,1.5,1.3,3.3,1.3,5.2C22.8,16,20.6,19.5,17.4,21.3z"/>
	</svg> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></div>
</nav>
<div class="content">
	<h1 class="title"><?php echo esc_html( $amp_post->get_title() ); ?></h1>
	<ul class="meta">
		<li class="byline">
			<?php echo $amp_post->get_author_avatar(); ?>
			<span class="author"><?php echo $amp_post->get_author_name(); ?></span>
			<span>&nbsp;&nbsp;&bull;</span>
		</li>
		<li><time datetime="<?php echo esc_attr( $amp_post->get_machine_date() ); ?>"><?php echo esc_html( $amp_post->get_human_date() ); ?></time></li>
	</ul>
	<?php echo $amp_post->get_content(); ?>
</div>
<!-- <?php printf( 'Generated in %ss', timer_stop() ); ?> -->
</body>
</html>
