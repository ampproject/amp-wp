<?php
/**
 * Style template.
 *
 * @package AMP
 */

/**
 * Context.
 *
 * @var AMP_Post_Template $this
 */
$link_color              = $this->get_customizer_setting( 'link_color' );
?>
body {
	text-align: right;
}

blockquote {
	border-left: 0;
	border-right: 2px solid <?php echo sanitize_hex_color( $link_color ); ?>;
}


.amp-wp-header .amp-wp-site-icon {	
	right: 0;
	left: 18px;
}


.amp-wp-article-header .amp-wp-meta:last-of-type {
	text-align: left;
}

.amp-wp-article-header .amp-wp-meta:first-of-type {
	text-align: right;
}

.amp-wp-byline amp-img {
	margin-right: 0;
	margin-left: 6px;
}

.amp-wp-posted-on {
	text-align: left;
}

.amp-wp-article-content ul,
.amp-wp-article-content ol {
	margin-left: 0;
	margin-right: 1em;
}

.amp-wp-article-content amp-img {
	margin: 0 auto;
}

.back-to-top {
	
	right: auto;
	right: 16px;
}
