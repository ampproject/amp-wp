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

		$this->scripts = array();

		$this->content = apply_filters( 'amp_post_content', $this->build_content(), $this->post );
		$this->metadata = apply_filters( 'amp_post_metadata', $this->build_metadata(), $this->post );
	}

	function get_canonical_url() {
		return $this->metadata['mainEntityOfPage'];
	}

	function get_title() {
		return $this->metadata['headline'];
	}

	function get_metadata() {
		return $this->metadata;
	}

	function get_author_avatar( $size = 24 ) {
		$avatar_html = get_avatar( $this->author->user_email, 24 );
		$converter = new AMP_Img_Converter( $avatar_html );
		return $converter->convert();
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
		$data = array(
			'@context' => 'http://schema.org',
			'@type' => 'BlogPosting', // TODO: change this for pages
			'mainEntityOfPage' => get_permalink( $this->ID ),
			'headline' => get_the_title( $this->ID ),
			'datePublished' => get_the_date( 'c', $this->ID ),
			'author' => array(
				'@type' => 'Person',
				'name' => $this->author->display_name,
			),
			'publisher' => array(
				'@type' => 'Organization',
				'name' => get_bloginfo( 'name' ),
			),
		);

		return $data;
	}

	private function build_content() {
		$amp = new AMP_Content( $this->post->post_content );
		$content = $amp->transform();
		$this->scripts = $amp->get_scripts();
		return $content;
	}

	private function add_script( $element, $script ) {
		if ( isset( $this->scripts[ $element ] ) ) {
			return;
		}
		$this->scripts[ $element ] = $script;
	}
}
