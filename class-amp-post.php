<?php

require( dirname( __FILE__ ) . '/class-amp-content.php' );

class AMP_Post {
	private $ID;
	private $post;
	private $author;
	private $content;
	private $metadata;
	private $scripts;

	function __construct( $post_id ) {
		$this->ID = $post_id;
		$this->post = get_post( $post_id );
		$this->author = get_userdata( $this->post->post_author );

		$amp_content = new AMP_Content( $this->post->post_content );
		$this->content = apply_filters( 'amp_post_content', $amp_content->transform(), $this->post );
		$this->scripts = apply_filters( 'amp_post_scripts', $amp_content->get_scripts(), $this->post );
		$this->metadata = apply_filters( 'amp_post_metadata', $this->build_metadata(), $this->post );
	}

	function get_canonical_url() {
		return $this->metadata['mainEntityOfPage'];
	}

	function get_id() {
		return $this->ID;
	}

	function get_title() {
		return $this->metadata['headline'];
	}

	function get_metadata() {
		return $this->metadata;
	}

	function get_author_avatar_url( $size = 24 ) {
		return get_avatar_url( $this->author->user_email, array(
			'size' => $size,
		) );
	}

	function get_author_name() {
		return $this->metadata['author']['name'];
	}

	function get_machine_date() {
		return $this->metadata['datePublished'];
	}

	function get_human_date() {
		return sprintf( _x( 'Posted %s ago', '%s = human-readable time difference', 'amp' ), human_time_diff( get_the_date( 'U', $this->ID ) ) );
	}

	function get_content() {
		return $this->content;
	}

	function get_scripts() {
		return $this->scripts;
	}

	private function build_metadata() {
		$metadata = array(
			'@context' => 'http://schema.org',
			'@type' => 'BlogPosting', // TODO: change this for pages
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
