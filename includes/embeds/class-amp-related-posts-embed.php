<?php

/**
 * Add related posts to AMP posts.
 */
class AMP_Related_Posts_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Remove Jetpack's native content filter and add our own.
	 */
	public function register_embed() {
		if ( class_exists( 'Jetpack_RelatedPosts' ) && method_exists( 'Jetpack_RelatedPosts', 'init' ) ) {
			// If we have an existing callback we are overriding, remove it.
			$jprp = Jetpack_RelatedPosts::init();
			remove_filter( 'the_content', array( $jprp, 'filter_add_target_to_dom' ) );

			// Add our new callbacks
			add_action( 'amp_post_template_css', array( $this, 'add_related_posts_styles' ) );
			add_filter( 'the_content', array( $this, 'add_related_posts' ) );
		}

		do_action( 'amp_related_posts_register_embed' );
	}

	/**
	 * Remove our own filter and re-register Jetpack's filter.
	 */
	public function unregister_embed() {
		if ( class_exists( 'Jetpack_RelatedPosts' ) && method_exists( 'Jetpack_RelatedPosts', 'init' ) ) {
			// Let's clean up after ourselves, just in case.
			$jprp = Jetpack_RelatedPosts::init();
			add_filter( 'the_content', array( $jprp, 'filter_add_target_to_dom' ) );
			remove_filter( 'the_content', array( $this, 'add_related_posts' ) );
		}

		do_action( 'amp_related_posts_unregister_embed' );
	}

	/**
	 * Get scripts that need to be added for the related posts template to work.
	 * 
	 * @return array The array of scripts that should be added.
	 */
	public function get_scripts() {
		$scripts = array(
			'amp-mustache' => 'https://cdn.ampproject.org/v0/amp-mustache-0.1.js',
			'amp-list' => 'https://cdn.ampproject.org/v0/amp-list-0.1.js',
		);
		return apply_filters( 'amp_related_posts_scripts', $scripts );
	}

	/**
	 * Get any options that might be needed to render the related posts.
	 * 
	 * @return array The array of options.
	 */
  	private function get_options() {
  		$defaults = array(
  			'size' => 3,
  			'show_thumbnails' => false,
  			'show_headline' => true,
  			'show_date' => true,
  			'show_context' => true,
  			'headline' => 'Related',
  		);

  		$options = array();
		if( class_exists( 'Jetpack_RelatedPosts' ) && method_exists( 'Jetpack_RelatedPosts', 'get_options' ) ) {
			$jprp = Jetpack_RelatedPosts::init();
			$options = $jprp->get_options();
		}

		$options = wp_parse_args( $options, $defaults );
		
		return apply_filters( 'amp_related_posts_options', $options );
	}

	/**
	 * Custom styles for use with the related posts template included below.
	 * 
	 * @param AMP_Post_Template $amp_template The instance of the template in use.
	 */
	public function add_related_posts_styles( $amp_template ) {

		$amp_related_posts_styles = <<<EOT
/* AMP Related Posts */

	.amp-related-posts {
		font-size: 0.8rem;
		line-height: 1.4285714286em;
		font-family: inherit;
		margin: 1em 0;
		padding: 1em 0;
	}
	.amp-related-posts-headline {
		margin: 1em 0;
		padding-top: 1.2em;
		display: inline-block;
		border-top: 1px rgba(0,0,0,0.3) solid;
	}
	.amp-related-posts-list > div {
		display: flex;
		justify-content: space-around;
		flex-wrap: wrap;
	}
	.amp-related-posts-item {
		max-width: 200px;
		overflow: hidden;
		position: relative;
		flex-grow: 1;
		flex-shrink: 1;
		opacity: 0.8
	}
	.amp-related-posts-item:hover {
		opacity: 1.0;
	}
	.amp-related-posts-item + .amp-related-posts-item {
		margin-left: 20px;
	}
	h4.amp-related-posts-title {
		margin: 0 0 1em;
		padding: 0;
	}
	.amp-related-posts-overlay {
		position: absolute;
		top: 0;
		bottom: 0;
		left: 0;
		right: 0;
		display: block;
	}
	.amp-related-posts-image {
		width: 200px;
		margin-bottom: 1em;
	}
	.amp-related-posts-excerpt {
		max-height: 7.1428571429em;
		overflow: hidden;
		display: block;
	}
	.amp-related-posts-date,
	.amp-related-posts-context {
		display: block;
		opacity: 0.6
	}
EOT;

		echo apply_filters( 'amp_related_posts_styles', $amp_related_posts_styles, $amp_template );
	}

	/**
	 * Get the url to retrieve the JSON for related posts.
	 *
	 * Note: According to the AMP spec, the list data returned from this endpoint
	 * must follow the 'items' key.
	 * 
	 * For more information see: https://github.com/ampproject/amphtml/blob/master/extensions/amp-list/amp-list.md
	 * 
	 * @return string The url of the API endpoint to retreieve related posts.
	 */
	public function get_related_posts_url() {
		$permalink = get_permalink();
		$parsed_permalink = wp_parse_url( $permalink );
		$related_posts_url = false;
		if ( 'http' == $parsed_permalink['scheme'] ) {
			$related_posts_url = '//';
		} elseif ( 'https' == $parsed_permalink['scheme'] ) {
			$related_posts_url = 'https://';
		} else {
			return false;
		}
		$related_posts_url .= $parsed_permalink['host'];
		if ( ! empty( $parsed_permalink['port'] ) ) {
			$related_posts_url .= ':' . $parsed_permalink['port'];
		}
		if ( ! empty( $parsed_permalink['path'] ) ) {
			$related_posts_url .= $parsed_permalink['path'];
		}
		if ( ! empty( $parsed_permalink['query'] ) ) {
			$related_posts_url .= '?' . $parsed_permalink['query'] . '&relatedposts=1';
		} else {
			$related_posts_url .= '?relatedposts=1';
		}

		return apply_filters( 'amp_related_posts_url', $related_posts_url );
	}

	/**
	 * Add related posts to the end of the post content.
	 * 
	 * @param string $content The post content.
	 */
	public function add_related_posts( $content ) {

		$options = $this->get_options();	
		$related_posts_url = $this->get_related_posts_url();

		$related_posts_wrap_begin = <<<EOT
<div class="amp-related-posts">
EOT;


		$related_posts_headline = sprintf(
			'<h3 class="amp-related-posts-headline"><em>%s</em></h3>',
			esc_html__( 'Related', 'jetpack' )
		);

		$related_posts_header_thumbs = <<<EOT
	<amp-list class="amp-related-posts-list" src="$related_posts_url" width="200" height="284" layout="responsive">
		<template type="amp-mustache">
			<div class="amp-related-posts-item" data-post-id="{{id}}" data-post-format="{{format}}">
EOT;

		$related_posts_header_excerpt = <<<EOT
	<amp-list class="amp-related-posts-list" src="$related_posts_url" width="200" height="171" layout="responsive">
		<template type="amp-mustache">
			<div class="amp-related-posts-item" data-post-id="{{id}}" data-post-format="{{format}}">
EOT;

		$related_posts_thumb = <<<EOT
				<a class="amp-related-posts-a" href="{{url}}" title="{{title}}&#13;&#13;{{excerpt}}" rel="{{rel}}" target="_self">
					{{#img.src}}
					<amp-img width="{{img.width}}" height="{{img.height}}" layout="responsive" alt="{{title}}" src="{{img.src}}"></amp-img>
					{{/img.src}}
				</a>
EOT;
			

		$related_posts_title = <<<EOT
				<h4 class="amp-related-posts-title"><a class="amp-relatedposts-post-a" href="{{url}}" title="{{title}}&#13;&#13;{{excerpt}}" rel="{{rel}}" target="_self">{{title}}</a></h4>
EOT;

		$related_posts_excerpt = <<<EOT
				<a class="amp-related-posts-overlay" href="{{url}}" title="{{title}}&#13;&#13;{{excerpt}}" rel="{{rel}}" target="_self"></a>
				<p class="amp-related-posts-excerpt">{{excerpt}}</p>
EOT;

		$related_posts_date = <<<EOT
				{{#date}}
				<div class="amp-related-posts-date">{{date}}</div>
				{{/date}}
EOT;

		$related_posts_context = <<<EOT
				{{#context}}
				<div class="amp-related-posts-context">{{context}}</div>
				{{/context}}
EOT;

		$related_posts_footer = <<<EOT
			</div>
		</template>
	</amp-list>
EOT;

		$related_posts_wrap_end = <<<EOT
<div>
EOT;

		$related_posts = apply_filters( 'amp_related_posts_template_wrap_begin', $related_posts_wrap_begin );
		if ( isset( $options['show_headline'] ) && ( true == $options['show_headline'] ) ) {
			$related_posts .= apply_filters( 'amp_related_posts_template_headline', $related_posts_headline );
		}
		if ( isset( $options['show_thumbnails'] ) && ( true == $options['show_thumbnails'] ) ) {
			$related_posts .= apply_filters( 'amp_related_posts_template_header_thumbs', $related_posts_header_thumbs );
		} else {
			$related_posts .= apply_filters( 'amp_related_posts_template_header_excerpt', $related_posts_header_excerpt );
		}
		if ( isset( $options['show_thumbnails'] ) && ( true == $options['show_thumbnails'] ) ) {
			$related_posts .= apply_filters( 'amp_related_posts_template_thumb', $related_posts_thumb );
		}
		$related_posts .= $related_posts_title;
		if ( isset( $options['show_thumbnails'] ) && ( false == $options['show_thumbnails'] ) ) {
			$related_posts .= apply_filters( 'amp_related_posts_template_excerpt', $related_posts_excerpt );
		}
		if ( isset( $options['show_date'] ) && ( true == $options['show_date'] ) ) {
			$related_posts .= apply_filters( 'amp_related_posts_template_date', $related_posts_date );
		}
		if ( isset( $options['show_context'] ) && ( true == $options['show_context'] ) ) {
			$related_posts .= apply_filters( 'amp_related_posts_template_context', $related_posts_context );
		}
		$related_posts .= apply_filters( 'amp_related_posts_template_footer', $related_posts_footer );
		$related_posts .= apply_filters( 'amp_related_posts_template_wrap_end', $related_posts_wrap_end );

		$content .= apply_filters( 'amp_related_posts_template', $related_posts, $options, $related_posts_url );

		return $content;
	}
}
