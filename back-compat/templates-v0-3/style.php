<?php
/**
 * Legacy template for the AMP stylesheet.
 *
 * @package AMP
 */

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped

?>
/* Merriweather fonts */
@font-face {
	font-family:'Merriweather';
	src:url('https://s1.wp.com/i/fonts/merriweather/merriweather-regular-webfont.woff2') format('woff2'),
		url('https://s1.wp.com/i/fonts/merriweather/merriweather-regular-webfont.woff') format('woff'),
		url('https://s1.wp.com/i/fonts/merriweather/merriweather-regular-webfont.ttf') format('truetype'),
		url('https://s1.wp.com/i/fonts/merriweather/merriweather-regular-webfont.svg#merriweatherregular') format('svg');
	font-weight:400;
	font-style:normal;
}

@font-face {
	font-family:'Merriweather';
	src:url('https://s1.wp.com/i/fonts/merriweather/merriweather-italic-webfont.woff2') format('woff2'),
		url('https://s1.wp.com/i/fonts/merriweather/merriweather-italic-webfont.woff') format('woff'),
		url('https://s1.wp.com/i/fonts/merriweather/merriweather-italic-webfont.ttf') format('truetype'),
		url('https://s1.wp.com/i/fonts/merriweather/merriweather-italic-webfont.svg#merriweatheritalic') format('svg');
	font-weight:400;
	font-style:italic;
}

@font-face {
	font-family:'Merriweather';
	src:url('https://s1.wp.com/i/fonts/merriweather/merriweather-bold-webfont.woff2') format('woff2'),
		url('https://s1.wp.com/i/fonts/merriweather/merriweather-bold-webfont.woff') format('woff'),
		url('https://s1.wp.com/i/fonts/merriweather/merriweather-bold-webfont.ttf') format('truetype'),
		url('https://s1.wp.com/i/fonts/merriweather/merriweather-bold-webfont.svg#merriweatherbold') format('svg');
	font-weight:700;
	font-style:normal;
}

@font-face {
	font-family:'Merriweather';
	src:url('https://s1.wp.com/i/fonts/merriweather/merriweather-bolditalic-webfont.woff2') format('woff2'),
		url('https://s1.wp.com/i/fonts/merriweather/merriweather-bolditalic-webfont.woff') format('woff'),
		url('https://s1.wp.com/i/fonts/merriweather/merriweather-bolditalic-webfont.ttf') format('truetype'),
		url('https://s1.wp.com/i/fonts/merriweather/merriweather-bolditalic-webfont.svg#merriweatherbold_italic') format('svg');
	font-weight:700;
	font-style:italic;
}

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

.amp-wp-unknown-size img {
	/** Worst case scenario when we can't figure out dimensions for an image. **/
	/** Force the image into a box of fixed dimensions and use object-fit to scale. **/
	object-fit: contain;
}

/* Template Styles */
.amp-wp-content, .amp-wp-title-bar div {
	<?php $content_max_width = absint( $this->get( 'content_max_width' ) ); ?>
	<?php if ( $content_max_width > 0 ) : ?>
	max-width: <?php echo sprintf( '%dpx', $content_max_width ); ?>;
	margin: 0 auto;
	<?php endif; ?>
}

body {
	font-family: 'Merriweather', Serif;
	font-size: 16px;
	line-height: 1.8;
	background: #fff;
	color: #3d596d;
	padding-bottom: 100px;
}

.amp-wp-content {
	padding: 16px;
	overflow-wrap: break-word;
	word-wrap: break-word;
	font-weight: 400;
	color: #3d596d;
}

.amp-wp-title {
	margin: 36px 0 0 0;
	font-size: 36px;
	line-height: 1.258;
	font-weight: 700;
	color: #2e4453;
}

.amp-wp-meta {
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


/* UI Fonts */
.amp-wp-meta,
nav.amp-wp-title-bar,
.wp-caption-text {
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen-Sans", "Ubuntu", "Cantarell", "Helvetica Neue", sans-serif;
	font-size: 15px;
}


/* Meta */
ul.amp-wp-meta {
	padding: 24px 0 0 0;
	margin: 0 0 24px 0;
}

ul.amp-wp-meta li {
	list-style: none;
	display: inline-block;
	margin: 0;
	line-height: 24px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	max-width: 300px;
}

ul.amp-wp-meta li:before {
	content: "\2022";
	margin: 0 8px;
}

ul.amp-wp-meta li:first-child:before {
	display: none;
}

.amp-wp-meta,
.amp-wp-meta a {
	color: #4f748e;
}

.amp-wp-meta .screen-reader-text {
	/* from twentyfifteen */
	clip: rect(1px, 1px, 1px, 1px);
	height: 1px;
	overflow: hidden;
	position: absolute;
	width: 1px;
}

.amp-wp-byline amp-img {
	border-radius: 50%;
	border: 0;
	background: #f3f6f8;
	position: relative;
	top: 6px;
	margin-right: 6px;
}

/* Titlebar */
nav.amp-wp-title-bar {
	background: <?php echo esc_html( $this->get_customizer_setting( 'navbar_background', self::DEFAULT_NAVBAR_BACKGROUND ) ); // not ideal for escaping here, but better than nothing? ?>;
	padding: 0 16px;
}

nav.amp-wp-title-bar div {
	line-height: 54px;
	color: <?php echo esc_html( $this->get_customizer_setting( 'navbar_color', self::DEFAULT_NAVBAR_COLOR ) ); ?>;
}

nav.amp-wp-title-bar a {
	color: <?php echo esc_html( $this->get_customizer_setting( 'navbar_color', self::DEFAULT_NAVBAR_COLOR ) ); ?>;
	text-decoration: none;
}

nav.amp-wp-title-bar .amp-wp-site-icon {
	/** site icon is 32px **/
	float: left;
	margin: 11px 8px 0 0;
	border-radius: 50%;
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

amp-iframe,
amp-youtube,
amp-instagram,
amp-vine {
	background: #f3f6f8;
}

amp-carousel > amp-img > img {
	object-fit: contain;
}

.amp-wp-iframe-placeholder {
	background: #f3f6f8 url( <?php echo esc_url( $this->get( 'placeholder_image_url' ) ); ?> ) no-repeat center 40%;
	background-size: 48px 48px;
	min-height: 48px;
}
