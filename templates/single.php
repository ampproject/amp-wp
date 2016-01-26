<!doctype html>
<html amp>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no,minimal-ui">
	<link href="https://fonts.googleapis.com/css?family=Merriweather:400,400italic,700,700italic|Open+Sans:400,700,400italic,700italic" rel="stylesheet" type="text/css">
	<?php do_action( 'amp_post_template_head', $this ); ?>
	<style>body {opacity: 0}</style><noscript><style>body {opacity: 1}</style></noscript>
	<style amp-custom>
	/* Generic WP styling */
	amp-img.alignright { float: right; margin: 0 0 1em 1em; }
	amp-img.alignleft { float: left; margin: 0 1em 1em 0; }
	amp-img.aligncenter { display: block; margin-left: auto; margin-right: auto; }
	.alignright { float: right; }
	.alignleft { float: left; }
	.aligncenter { display: block; margin-left: auto; margin-right: auto; }

	.wp-caption.alignleft { margin-right: 1em; }
	.wp-caption.alignright { margin-left: 1em; }

	.amp-wp-enforced-sizes {
		/** Our sizes fallback is 100vw, and we have a padding on the container; the max-width here prevents the element from overflowing. **/
		max-width: 100%;
	}

	/* Generic WP.com reader style */
	.content, .title-bar div {
		max-width: <?php echo sprintf( '%dpx', absint( $this->get( 'content_max_width' ) ) ); ?>;
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
		margin: 0;
		line-height: 24px;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		max-width: 300px;
	}

	ul.meta li:before {
		content: "\2022";
		margin: 0 8px;
	}

	ul.meta li:first-child:before {
		display: none;
	}

	.meta,
	.meta a {
		color: #4f748e;
	}

	.meta .screen-reader-text {
		/* from twentyfifteen */
		clip: rect(1px, 1px, 1px, 1px);
		height: 1px;
		overflow: hidden;
		position: absolute !important;
		width: 1px;
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

	nav.title-bar a {
		color: #fff;
		text-decoration: none;
	}

	nav.title-bar svg {
		fill: #fff;
		float: left;
		margin: 11px 8px 0 0;
	}


	/* Captions */
	.wp-caption-text {
		padding: 8px 16px;
		font-style: italic;
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

	/* Other Elements */
	amp-carousel {
		background: #000;
	}

	amp-img {
		background: #f3f6f8;
	}

	.amp-wp-iframe-placeholder {
		background: #f3f6f8 url( <?php echo esc_url( $this->get( 'placeholder_image_url' ) ); ?> ) no-repeat center center;
		background-size: 48px 48px;
		min-height: 48px;
	}
	</style>
</head>
<body>
<nav class="title-bar">
	<div>
		<a href="<?php echo esc_url( $this->get( 'home_url' ) ); ?>">
			<svg x="0px" y="0px" width="24" height="24" viewBox="0 0 24 24">
				<path class="st0" d="M12,0C5.4,0,0,5.4,0,12c0,6.6,5.4,12,12,12c6.6,0,12-5.4,12-12C24,5.4,18.6,0,12,0z M1.2,12
					c0-1.6,0.3-3,0.9-4.4l5.1,14.1C3.7,20,1.2,16.3,1.2,12z M12,22.8c-1.1,0-2.1-0.2-3-0.4l3.2-9.4l3.3,9.1c0,0.1,0,0.1,0.1,0.1
					C14.5,22.6,13.3,22.8,12,22.8z M13.5,6.9c0.6,0,1.2-0.1,1.2-0.1c0.6-0.1,0.5-0.9-0.1-0.9c0,0-1.7,0.1-2.9,0.1
					c-1.1,0-2.8-0.1-2.8-0.1c-0.6,0-0.7,0.9-0.1,0.9c0,0,0.6,0.1,1.1,0.1l1.7,4.6l-2.4,7.1L5.4,6.9c0.7,0,1.2-0.1,1.2-0.1
					c0.6-0.1,0.5-0.9-0.1-0.9c0,0-1.7,0.1-2.9,0.1c-0.2,0-0.4,0-0.7,0c1.9-2.9,5.2-4.9,9-4.9c2.8,0,5.4,1.1,7.3,2.8c0,0-0.1,0-0.1,0
					c-1.1,0-1.8,0.9-1.8,1.9c0,0.9,0.5,1.6,1.1,2.5c0.4,0.7,0.9,1.6,0.9,3c0,0.9-0.3,2.1-0.8,3.5l-1.1,3.6L13.5,6.9z M17.4,21.3
					l3.3-9.5c0.6-1.5,0.8-2.8,0.8-3.9c0-0.4,0-0.8-0.1-1.1c0.8,1.5,1.3,3.3,1.3,5.2C22.8,16,20.6,19.5,17.4,21.3z"/>
			</svg>
			<?php echo esc_html( $this->get( 'blog_name' ) ); ?>
		</a>
	</div>
</nav>
<div class="content">
	<h1 class="title"><?php echo esc_html( $this->get( 'post_title' ) ); ?></h1>
	<ul class="meta">
		<?php $this->load_parts( apply_filters( 'amp_post_template_meta_parts', array( 'meta-author', 'meta-time', 'meta-taxonomy' ) ) ); ?>
	</ul>
	<?php echo $this->get( 'post_amp_content' ); // amphtml content; no kses ?>
</div>
<?php do_action( 'amp_post_template_footer', $this ); ?>
</body>
</html>
