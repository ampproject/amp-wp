<?php

// Navbar background color
$amp_navbar_background_color = get_theme_mod( 'amp_navbar_background_color', '#0087be' );

// Navbar background color
$amp_navbar_background_image = get_theme_mod( 'amp_navbar_background_image', '' );

// Link Color
$amp_link_color = $amp_navbar_background_color;

// Navbar Text Color
$amp_navbar_color = get_theme_mod( 'amp_navbar_color', '#ffffff' );

// Theme color settings: Dark || Light
$theme_color_setting = get_theme_mod( 'amp_background_color', 'light' );

// Set text and border color based on $theme_color_setting
if ( $theme_color_setting == 'light' ) {

	// Convert colors to greyscale for light theme color
	// src: http://goo.gl/2gDLsp
	$theme_color      = '#fff';
	$text_color       = '#535353';
	$muted_text_color = '#9f9f9f';
	$border_color     = '#d4d4d4';

} elseif ( $theme_color_setting == 'dark' ) {

	// Convert and invert colors to greyscale for dark theme color
	// src: http://goo.gl/uVB2cO
	$theme_color      = '#111';
	$text_color       = '#acacac';
	$muted_text_color = '#606060';
	$border_color     = '#2b2b2b';

} else {

	// Default Calypso-based colors
	$theme_color      = '#fff';
	$text_color       = '#3d596d';
	$muted_text_color = '#87A6BC';
	$border_color     = '#c8d7e1';

} ?>

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

amp-img.alignright {
	float: right;
	margin: 0 0 1em 16px;
}

amp-img.alignleft {
	float: left;
	margin: 0 16px 1em 0;
}

amp-img.aligncenter {
	display: block;
	margin-left: auto;
	margin-right: auto;
}

.alignright {
	float: right;
}

.alignleft {
	float: left;
}

.aligncenter {
	display: block;
	margin-left: auto;
	margin-right: auto;
}

.wp-caption.alignleft {
	margin-right: 16px;
}

.wp-caption.alignright {
	margin-left: 16px;
}

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

.amp-wp-content,
.amp-wp-title-bar div {
	<?php $content_max_width = absint( $this->get( 'content_max_width' ) ); ?>
	<?php if ( $content_max_width > 0 ) : ?>
	margin: 0 auto;
	max-width: <?php echo sprintf( '%dpx', $content_max_width ); ?>;
	<?php endif; ?>
}

body {
	background: <?php echo $theme_color; ?>;
	color: <?php echo $text_color; ?>;
	font-family: 'Merriweather', 'Times New Roman', Times, Serif;
	font-weight: 300;
	line-height: 1.75em;
}

p,
ol,
ul,
figure {
	margin: 0 0 1em;
	padding: 0;
}

a,
a:visited {
	color: <?php echo $amp_link_color; ?>;
}

a:hover,
a:active,
a:focus {
	color: <?php echo $text_color; ?>;
}

/* Quotes */

blockquote {
	color: <?php echo $text_color; ?>;
	background: rgba(127,127,127,.125);
	border-left: 2px solid <?php echo $amp_link_color; ?>;
	margin: 8px 0 24px 0;
	padding: 16px;
}

blockquote p:last-child {
	margin-bottom: 0;
}

/* UI Fonts */

.amp-wp-meta,
.amp-wp-header div,
.amp-wp-title,
.wp-caption-text,
.amp-wp-share-links,
.amp-wp-tax-category,
.amp-wp-tax-tag,
.amp-wp-comment-link,
.amp-wp-footer p {
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen-Sans", "Ubuntu", "Cantarell", "Helvetica Neue", sans-serif;
}

/* Header */

.amp-wp-header {
	background-color: <?php echo $amp_navbar_background_color; ?>;
}

.amp-wp-header.header-background-image {
	background-image: url(<?php echo $amp_navbar_background_image; ?>);
	background-repeat: no-repeat;
	background-position: center center;
	background-size: cover;
}

.amp-wp-header div {
	color: <?php echo $amp_navbar_color; ?>;
	font-size: 1em;
	font-weight: 400;
	margin: 0 auto;
	max-width: calc(840px - 32px);
	padding: .875em 16px;
	position: relative;
}

.amp-wp-header a {
	color: <?php echo $amp_navbar_color; ?>;
	text-decoration: none;
}

.amp-wp-header .amp-wp-site-icon {
	/** site icon is 32px **/
	background-color: <?php echo $amp_navbar_color; ?>;
	border: 1px solid <?php echo $amp_navbar_color; ?>;
	border-radius: 50%;
	position: absolute;
	right: 18px;
	top: 10px;
}

/* Article */

.amp-wp-article {
	color: <?php echo $text_color; ?>;
	font-weight: 400;
	margin: 1.5em auto;
	max-width: 840px;
	overflow-wrap: break-word;
	word-wrap: break-word;
}

/* Article Header */

.amp-wp-article-header {
	align-items: center;
	align-content: stretch;
	display: flex;
	flex-wrap: wrap;
	justify-content: space-between;
	margin: 1.5em 16px 1.5em;
}

.amp-wp-title {
	color: <?php echo $text_color; ?>;
	display: block;
	flex: 1 0 100%;
	font-weight: 900;
	margin: 0 0 .625em;
	width: 100%;
}

/* Article Meta */

.amp-wp-meta {
	color: <?php echo $muted_text_color; ?>;
	display: inline-block;
	flex: 2 1 50%;
	font-size: .875em;
	line-height: 1.5em;
	margin: 0;
	padding: 0;
}

.amp-wp-article-header .amp-wp-meta:last-of-type {
	text-align: right;
}

.amp-wp-article-header .amp-wp-meta:first-of-type {
	text-align: left;
}

.amp-wp-byline amp-img,
.amp-wp-byline .amp-wp-author {
	display: inline-block;
	vertical-align: middle;
}

.amp-wp-byline amp-img {
	border: 1px solid <?php echo $amp_link_color; ?>;
	border-radius: 50%;
	position: relative;
	margin-right: 6px;
}

.amp-wp-posted-on {
	text-align: right;
}

/* Featured image */

.amp-wp-article-featured-image {
	margin: 0 0 1em;
}
.amp-wp-article-featured-image amp-img {
	border: 1px solid <?php echo $border_color; ?>;
	margin: -1px;
}
.amp-wp-article-featured-image.wp-caption .wp-caption-text {
	margin: 0 18px;
}

/* Article Content */

.amp-wp-article-content {
	margin: 0 16px;
}

.amp-wp-article-content ul,
.amp-wp-article-content ol {
	margin-left: 1em;
}

.amp-wp-article-content amp-img {
	border: 1px solid <?php echo $border_color; ?>;
}

/* Captions */

.wp-caption {
	padding: 0;
}

.wp-caption .wp-caption-text {
	border-bottom: 1px solid <?php echo $border_color; ?>;
	color: <?php echo $muted_text_color; ?>;
	font-size: .875em;
	line-height: 1.5em;
	margin: 0;
	padding: .66em 10px .75em;
}

/* AMP Media */

amp-carousel {
	background: <?php echo $border_color; ?>;
	margin: 0 -16px 1.5em;
}
amp-iframe,
amp-youtube,
amp-instagram,
amp-vine {
	background: <?php echo $border_color; ?>;
	margin: 0 -16px 1.5em;
}

.amp-wp-article-content amp-carousel amp-img {
	border: none;
}

amp-carousel > amp-img > img {
	object-fit: contain;
}

.amp-wp-iframe-placeholder {
	background: <?php echo $border_color; ?> url( <?php echo esc_url( $this->get( 'placeholder_image_url' ) ); ?> ) no-repeat center 40%;
	background-size: 48px 48px;
	min-height: 48px;
}

/* Article Footer Meta */

.amp-wp-article-footer .amp-wp-meta {
	display: block;
}

.amp-wp-share-links,
.amp-wp-tax-category,
.amp-wp-tax-tag {
	color: <?php echo $muted_text_color; ?>;
	font-size: .875em;
	line-height: 1.5em;
	margin: 1.5em 16px;
}

.amp-wp-share-links {
	vertical-align: middle;
}

amp-social-share {
	background-size: 66.66%;
	border-radius: 50%;
	display: inline-block;
	margin: 0 2px;
	vertical-align: middle;
}

.amp-wp-share-links span {
	clear: both;
	display: block;
	margin-bottom: .75em;
}

.amp-wp-comment-link {
	color: <?php echo $muted_text_color; ?>;
	font-size: .875em;
	line-height: 1.5em;
	text-align: center;
	margin: 2.25em 0 1.5em;
}

.amp-wp-comment-link a {
	border-style: solid;
	border-color: <?php echo $border_color; ?>;
	border-width: 1px 1px 2px;
	border-radius: 4px;
	background-color: transparent;
	color: <?php echo $text_color; ?>;
	cursor: pointer;
	display: block;
	font-size: 14px;
	font-weight: 600;
	line-height: 18px;
	margin: 0 auto;
	max-width: 200px;
	padding: 11px 16px;
	text-decoration: none;
	width: 50%;
	-webkit-transition: background-color 0.2s ease;
			transition: background-color 0.2s ease;
}

/* AMP Ad */

.adspace {
	border-top: 1px solid <?php echo $border_color; ?>;
	margin: 1.5em auto;
	padding-top: 1.5em;
	position: relative;
	text-align: center;
}
.adspace amp-ad {
	display: block;
	margin: 0 auto;
}

/* AMP Footer */

.amp-wp-footer {
	border-top: 1px solid <?php echo $border_color; ?>;
	margin: calc(1.5em - 1px) 0 0;
}

.amp-wp-footer div {
	margin: 0 auto;
	max-width: calc(840px - 32px);
	padding: 1.5em 16px 1.25em;
	position: relative;
}

.amp-wp-footer h2 {
	font-size: 1em;
	line-height: 1.125em;
	margin: 0 0 .5em;
}

.amp-wp-footer p {
	color: <?php echo $muted_text_color; ?>;
	font-size: .875em;
	line-height: 1.5em;
	margin: 0 100px 0 0;
}

.back-to-top {
	bottom: 1.5em;
	font-size: .875em;
	line-height: 2em;
	position: absolute;
	right: 16px;
}