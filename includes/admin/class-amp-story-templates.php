<?php
/**
 * AMP Story Templates handler.
 *
 * @package AMP
 * @since 1.?
 */

/**
 * Class AMP_Story_Templates
 */
class AMP_Story_Templates {

	/**
	 * Init.
	 */
	public function init() {
		if ( ! post_type_exists( 'amp_story' ) ) {
			return;
		}
		$this->maybe_import_story_templates();
	}

	/**
	 * Import story templates if they haven't been imported previously.
	 */
	public function maybe_import_story_templates() {
		if ( true === AMP_Options_Manager::get_option( 'has_created_templates' ) ) {
			return;
		}
		// @todo Update the option to true so that it wouldn't import again.
		$this->import_story_templates();
	}

	/**
	 * Import logic for story templates.
	 */
	public function import_story_templates() {
		$templates = self::get_story_templates();
		foreach ( $templates as $template ) {
			$existing_template = $this->template_exists( $template['title'], $template['content'] );
			if ( $existing_template > 0 ) {
				continue;
			}
			wp_insert_post(
				array(
					'post_title'   => $template['title'],
					'post_type'    => 'wp_block',
					'post_status'  => 'publish',
					'post_content' => $template['content'],
				)
			);
		}
	}

	/**
	 * Get the static list of templates, including temlate name and content.
	 *
	 * @return array Story templates.
	 */
	public static function get_story_templates() {
		return array(
			array(
				'title'   => __( 'Template 0', 'amp' ),
				'content' => '<!-- wp:amp/amp-story-page {"mediaId":58,"mediaType":"image","autoAdvanceAfterDuration":0} -->
<amp-story-page style="background-color:#ffffff" id="b4a914ac-febe-4315-b648-87e58abb9b61" class="wp-block-amp-amp-story-page"><amp-story-grid-layer template="fill"><amp-img layout="fill" src="https://amp.wordpress.test/wp-content/uploads/2018/12/2017-thumb.gif"></amp-img></amp-story-grid-layer><amp-story-grid-layer template="vertical"><!-- wp:amp/amp-story-text {"tagName":"h1","autoFontSize":28,"textColor":"very-light-gray","positionTop":10} -->
<h1 style="font-size:28px;width:76%;height:9%;position:absolute;top:10%;left:5%" class="has-text-color has-very-light-gray-color" id="890a40b8-1396-4d37-b61d-bc357c6c3850"><amp-fit-text layout="fill" class="amp-text-content">Hello Templates!</amp-fit-text></h1>
<!-- /wp:amp/amp-story-text --></amp-story-grid-layer></amp-story-page>
<!-- /wp:amp/amp-story-page -->',
			),
		);
	}

	/**
	 * Determine if a post exists based on title, content, only receives a published post.
	 *
	 * Note that this is almost a copy of the default `post_exists` function except for the post status.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param string $title Post title.
	 * @param string $content Post content.
	 * @return int Post ID if post exists, 0 otherwise.
	 */
	private function template_exists( $title, $content ) {
		if ( empty( $title ) || empty( $content ) ) {
			return 0;
		}
		global $wpdb;

		$post_title   = wp_unslash( sanitize_post_field( 'post_title', $title, 0, 'db' ) );
		$post_content = wp_unslash( sanitize_post_field( 'post_content', $content, 0, 'db' ) );

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE 1=1  AND post_title = %s AND post_content = %s AND post_status = 'publish'",
				array( $post_title, $post_content )
			)
		);
	}
}
