<?php

require( dirname( __FILE__ ) . '/class-amp-content.php' );

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

		$this->author = apply_filters( 'amp_post_author', get_userdata( $this->post->post_author ) );

		$this->content_max_width = apply_filters( 'amp_content_max_width', isset( $GLOBALS['content_width'] ) ? absint( $GLOBALS['content_width'] ) : 600 );
		$amp_content = new AMP_Content( $this->post->post_content, array(
			'content_max_width' => $this->content_max_width,
		) );
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
		return $this->content_max_width();
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

		return $metadata;
	}
}
