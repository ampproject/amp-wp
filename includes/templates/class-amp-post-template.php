<?php

require_once ( AMP__DIR__ . '/includes/utils/class-amp-dom-utils.php' );
require_once ( AMP__DIR__ . '/includes/utils/class-amp-html-utils.php' );
require_once ( AMP__DIR__ . '/includes/utils/class-amp-string-utils.php' );
require_once( AMP__DIR__ . '/includes/utils/class-amp-wp-utils.php' );

require_once ( AMP__DIR__ . '/includes/class-amp-content.php' );

// Require AMP filters
require_once( AMP__DIR__ . '/includes/sanitizers/require-sanitizers.php' );
// Require embed classes
require_once ( AMP__DIR__ . '/includes/embeds/require-embeds.php' );
// Require base AMP template class
require_once ( AMP__DIR__ . '/includes/templates/class-amp-template.php' );

class AMP_Post_Template extends AMP_Template {

	public function __construct( $post_id ) {

		parent::__construct();
		
		$this->post_id = $post_id;
		$this->post = get_post( $post_id );
		
		$this->data = array_merge(
			$this->amp_base_template_data,
			array(
				'document_title' => function_exists( 'wp_get_document_title' ) ? wp_get_document_title() : wp_title( '', false ), // back-compat with 4.3
				'canonical_url' => get_permalink( $post_id ),
				'html_tag_attributes' => array(),
				'site_icon_url' => apply_filters( 'amp_site_icon_url', function_exists( 'get_site_icon_url' ) ? get_site_icon_url( self::SITE_ICON_SIZE ) : '' ),
				'placeholder_image_url' => amp_get_asset_url( 'images/placeholder-icon.png' ),
				'featured_image' => false,
				'comments_link_url' => false,
				'comments_link_text' => false,
				/**
				 * Add amp-analytics tags.
				 *
				 * This filter allows you to easily insert any amp-analytics tags without needing much heavy lifting.
				 *
				 * @since 0.4
				 *.
				 * @param	array	$analytics	An associative array of the analytics entries we want to output. Each array entry must have a unique key, and the value should be an array with the following keys: `type`, `attributes`, `script_data`. See readme for more details.
				 * @param	object	$post	The current post.
				 */
				'amp_analytics' => apply_filters( 'amp_post_template_analytics', array(), $this->post ),
			)
		);

		$this->build_post_content();
		$this->build_post_data();
		$this->build_customizer_settings();
		$this->build_html_tag_attributes();

		$this->data = apply_filters( 'amp_post_template_data', $this->data, $this->post );
	}

	public function load_post_template() {
		$this->load( 'single' );
	}

	private function build_post_data() {
		$post_title = get_the_title( $this->post_id );
		$post_publish_timestamp = get_the_date( 'U', $this->post_id );
		$post_modified_timestamp = get_post_modified_time( 'U', false, $this->post );
		$post_author = get_userdata( $this->post->post_author );

		$this->add_data( array(
			'post' => $this->post,
			'post_id' => $this->post_id,
			'post_title' => $post_title,
			'post_publish_timestamp' => $post_publish_timestamp,
			'post_modified_timestamp' => $post_modified_timestamp,
			'post_author' => $post_author,
		) );

		$metadata = array(
			'@context' => 'http://schema.org',
			'@type' => 'BlogPosting',
			'mainEntityOfPage' => $this->get( 'canonical_url' ),
			'publisher' => array(
				'@type' => 'Organization',
				'name' => $this->get( 'blog_name' ),
			),
			'headline' => $post_title,
			'datePublished' => date( 'c', $post_publish_timestamp ),
			'dateModified' => date( 'c', $post_modified_timestamp ),
			'author' => array(
				'@type' => 'Person',
				'name' => $post_author->display_name,
			),
		);

		$site_icon_url = $this->get( 'site_icon_url' );
		if ( $site_icon_url ) {
			$metadata['publisher']['logo'] = array(
				'@type' => 'ImageObject',
				'url' => $site_icon_url,
				'height' => self::SITE_ICON_SIZE,
				'width' => self::SITE_ICON_SIZE,
			);
		}

		$image_metadata = $this->get_post_image_metadata();
		if ( $image_metadata ) {
			$metadata['image'] = $image_metadata;
		}

		$this->add_data_by_key( 'metadata', apply_filters( 'amp_post_template_metadata', $metadata, $this->post ) );

		$this->build_post_featured_image();
		$this->build_post_commments_data();
	}

	private function build_post_commments_data() {
		if ( ! post_type_supports( $this->post->post_type, 'comments' ) ) {
			return;
		}

		$comments_open = comments_open( $this->post_id );

		// Don't show link if close and no comments
		if ( ! $comments_open
			&& ! $this->post->comment_count ) {
			return;
		}

		$comments_link_url = get_comments_link( $this->post_id );
		$comments_link_text = $comments_open
			? __( 'Leave a Comment', 'amp' )
			: __( 'View Comments', 'amp' );

		$this->add_data( array(
			'comments_link_url' => $comments_link_url,
			'comments_link_text' => $comments_link_text,
		) );
	}

	private function build_post_content() {
		$amp_content = new AMP_Content( $this->post->post_content,
			apply_filters( 'amp_content_embed_handlers', array(
				'AMP_Twitter_Embed_Handler' => array(),
				'AMP_YouTube_Embed_Handler' => array(),
				'AMP_DailyMotion_Embed_Handler' => array(),
				'AMP_Vimeo_Embed_Handler' => array(),
				'AMP_SoundCloud_Embed_Handler' => array(),
				'AMP_Instagram_Embed_Handler' => array(),
				'AMP_Vine_Embed_Handler' => array(),
				'AMP_Facebook_Embed_Handler' => array(),
				'AMP_Pinterest_Embed_Handler' => array(),
				'AMP_Gallery_Embed_Handler' => array(),
			), $this->post ),
			apply_filters( 'amp_content_sanitizers', array(
				 'AMP_Style_Sanitizer' => array(),
				 // 'AMP_Blacklist_Sanitizer' => array(),
				 'AMP_Img_Sanitizer' => array(),
				 'AMP_Video_Sanitizer' => array(),
				 'AMP_Audio_Sanitizer' => array(),
				 'AMP_Playbuzz_Sanitizer' => array(),
				 'AMP_Iframe_Sanitizer' => array(
					 'add_placeholder' => true,
				 ),
				 'AMP_Tag_And_Attribute_Sanitizer' => array(),
			), $this->post ),
			array(
				'content_max_width' => $this->get( 'content_max_width' ),
			)
		);

		$this->add_data_by_key( 'post_amp_content', $amp_content->get_amp_content() );
		$this->merge_data_for_key( 'amp_component_scripts', $amp_content->get_amp_scripts() );
		$this->merge_data_for_key( 'amp_styles', $amp_content->get_amp_styles() );
	}

	private function build_post_featured_image() {
		$post_id = $this->post_id;
		$featured_html = get_the_post_thumbnail( $post_id, 'large' );

		// Skip featured image if no featured image is available.
		if ( ! $featured_html ) {
			return;
		}

		$featured_id = get_post_thumbnail_id( $post_id );

		// If an image with the same ID as the featured image exists in the content, skip the featured image markup.
		// Prevents duplicate images, which is especially problematic for photo blogs.
		// A bit crude but it's fast and should cover most cases.
		$post_content = $this->post->post_content;
		if ( false !== strpos( $post_content, 'wp-image-' . $featured_id )
			|| false !== strpos( $post_content, 'attachment_' . $featured_id ) ) {
			return;
		}

		$featured_image = get_post( $featured_id );

		list( $sanitized_html, $featured_scripts, $featured_styles ) = AMP_Content_Sanitizer::sanitize(
			$featured_html,
			array( 'AMP_Img_Sanitizer' => array() ),
			array(
				'content_max_width' => $this->get( 'content_max_width' ),
			)
		);

		$this->add_data_by_key( 'featured_image', array(
			'amp_html' => $sanitized_html,
			'caption' => $featured_image->post_excerpt,
		) );

		if ( $featured_scripts ) {
			$this->merge_data_for_key( 'amp_component_scripts', $featured_scripts );
		}

		if ( $featured_styles ) {
			$this->merge_data_for_key( 'amp_styles', $featured_styles );
		}
	}

	private function build_customizer_settings() {
		$settings = AMP_Customizer_Settings::get_settings();

		/**
		 * Filter AMP Customizer settings.
		 *
		 * Inject your Customizer settings here to make them accessible via the getter in your custom style.php template.
		 *
		 * Example:
		 *
		 *     echo esc_html( $this->get_customizer_setting( 'your_setting_key', 'your_default_value' ) );
		 *
		 * @since 0.4
		 *
		 * @param array   $settings Array of AMP Customizer settings.
		 * @param WP_Post $post     Current post object.
		 */
		$this->add_data_by_key( 'customizer_settings', apply_filters( 'amp_post_template_customizer_settings', $settings, $this->post ) );
	}

	/**
	 * Grabs featured image or the first attached image for the post
	 *
	 * TODO: move to a utils class?
	 */
	private function get_post_image_metadata() {
		$post_image_meta = null;
		$post_image_id = false;

		if ( has_post_thumbnail( $this->post_id ) ) {
			$post_image_id = get_post_thumbnail_id( $this->post_id );
		} else {
			$attached_image_ids = get_posts( array(
				'post_parent' => $this->post_id,
				'post_type' => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 1,
				'orderby' => 'menu_order',
				'order' => 'ASC',
				'fields' => 'ids',
				'suppress_filters' => false,
			) );

			if ( ! empty( $attached_image_ids ) ) {
				$post_image_id = array_shift( $attached_image_ids );
			}
		}

		if ( ! $post_image_id ) {
			return false;
		}

		$post_image_src = wp_get_attachment_image_src( $post_image_id, 'full' );

		if ( is_array( $post_image_src ) ) {
			$post_image_meta = array(
				'@type' => 'ImageObject',
				'url' => $post_image_src[0],
				'width' => $post_image_src[1],
				'height' => $post_image_src[2],
			);
		}

		return $post_image_meta;
	}

	private function build_html_tag_attributes() {
		$attributes = array();

		if ( function_exists( 'is_rtl' ) && is_rtl() ) {
			$attributes['dir'] = 'rtl';
		}

		$lang = get_bloginfo( 'language' );
		if ( $lang ) {
			$attributes['lang'] = $lang;
		}

		$this->add_data_by_key( 'html_tag_attributes', $attributes );
	}
}
