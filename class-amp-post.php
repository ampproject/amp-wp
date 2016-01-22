<?php

require( dirname( __FILE__ ) . '/class-amp-content.php' );

require_once( dirname( __FILE__ ) . '/includes/sanitizers/class-amp-blacklist-sanitizer.php' );
require_once( dirname( __FILE__ ) . '/includes/sanitizers/class-amp-img-sanitizer.php' );
require_once( dirname( __FILE__ ) . '/includes/sanitizers/class-amp-video-sanitizer.php' );
require_once( dirname( __FILE__ ) . '/includes/sanitizers/class-amp-iframe-sanitizer.php' );
require_once( dirname( __FILE__ ) . '/includes/sanitizers/class-amp-audio-sanitizer.php' );

require_once( dirname( __FILE__ ) . '/includes/embeds/class-amp-twitter-embed.php' );
require_once( dirname( __FILE__ ) . '/includes/embeds/class-amp-youtube-embed.php' );
require_once( dirname( __FILE__ ) . '/includes/embeds/class-amp-gallery-embed.php' );
require_once( dirname( __FILE__ ) . '/includes/embeds/class-amp-instagram-embed.php' );

class AMP_Post {
	private $ID;
	private $post;
	private $author;
	private $content;
	private $metadata;
	private $scripts;
	private $content_max_width;

	function __construct( $post_id ) {
		$this->ID = $post_id;
		$this->post = get_post( $post_id );

		$this->author = apply_filters( 'amp_post_author', get_userdata( $this->post->post_author ), $this->post );

		$content_width = isset( $GLOBALS['content_width'] ) ? absint( $GLOBALS['content_width'] ) : 600;
		$this->content_max_width = apply_filters( 'amp_content_max_width', $content_width, $this->post );

		$amp_content = new AMP_Content( $this->post->post_content,
			apply_filters( 'amp_content_embed_handlers', array(
				'AMP_Twitter_Embed_Handler', 'AMP_YouTube_Embed_Handler', 'AMP_Gallery_Embed_Handler', 'AMP_Instagram_Embed_Handler'
			), $this->post ),
			apply_filters( 'amp_content_sanitizers', array(
				 'AMP_Blacklist_Sanitizer', 'AMP_Img_Sanitizer', 'AMP_Video_Sanitizer', 'AMP_Audio_Sanitizer', 'AMP_Iframe_Sanitizer'
			), $this->post ),
			array(
				'content_max_width' => $this->content_max_width,
			)
		);

		$this->content = apply_filters( 'amp_post_content', $amp_content->transform(), $this->post );
		$this->scripts = apply_filters( 'amp_post_scripts', $amp_content->get_scripts(), $this->post );
		$this->metadata = apply_filters( 'amp_post_metadata', $this->build_metadata(), $this->post );
	}

	function get_ID() {
		return $this->ID;
	}

	function get_post() {
		return $this->post;
	}

	function get_author() {
		return $this->author;
	}

	function get_scripts() {
		return $this->scripts;
	}

	function get_metadata() {
		return $this->metadata;
	}

	function get_content() {
		return $this->content;
	}

	function get_content_max_width() {
		return $this->content_max_width;
	}

	private function build_metadata() {
		$metadata = array(
			'@context' => 'http://schema.org',
			'@type' => 'BlogPosting',
			'mainEntityOfPage' => get_permalink( $this->ID ),
			'headline' => get_the_title( $this->ID ),
			'datePublished' => get_the_date( 'c', $this->ID ),
			'dateModified' => get_post_modified_time( 'c', false, $this->post ),
			'author' => array(
				'@type' => 'Person',
				'name' => $this->author->display_name,
			),
			'publisher' => array(
				'@type' => 'Organization',
				'name' => get_bloginfo( 'name' ),
			),
		);
		
		$post_thumbnail_id = false;

		// Include a reference to either a featured image or the first attached image.
		if ( has_post_thumbnail( $this->ID ) ) {
			$post_thumbnail_id = get_post_thumbnail_id( $this->ID );
		}
		else {
			$attached_media = get_attached_media( 'image', $this->ID );

			if ( $attached_media ) {
				$first_attachment = array_shift( $attached_media ); 
				$post_thumbnail_id = $first_attachment->ID;
			}
		}

		if ( $post_thumbnail_id ) {
			$post_thumbnail = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );

			if ( $post_thumbnail ) {
				$metadata['image'] = array(
					'@type' => 'ImageObject',
					'url' => $post_thumbnail[0],
					'width' => $post_thumbnail[1],
					'height' => $post_thumbnail[2],
				);
			}
		}

		return $metadata;
	}
}
