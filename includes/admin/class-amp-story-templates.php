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
		$this->import_story_templates();
		// @todo This is commented out for testing purposes until the PR gets ready.
		// AMP_Options_Manager::update_option( 'has_created_templates', true );
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
				'title'   => __( 'Default Template 1', 'amp' ),
				'content' => '<!-- wp:amp/amp-story-page {"backgroundColor":"#313131","autoAdvanceAfterDuration":0} -->
<amp-story-page style="background-color:#313131" id="e7ae309f-d9f1-480a-a35e-7a495c2e09f7" class="wp-block-amp-amp-story-page"><amp-story-grid-layer template="vertical"><!-- wp:amp/amp-story-text {"tagName":"h1","autoFontSize":28,"ampFontFamily":"Garamond","textColor":"very-light-gray","positionTop":27,"positionLeft":13} -->
<h1 style="font-size:28px;width:76%;height:9%;position:absolute;top:27%;left:13%" class="has-text-color has-very-light-gray-color" id="ddce50c3-5776-44c5-9d9b-0d6d4b12d88e" data-font-family="Garamond"><amp-fit-text layout="fill" class="amp-text-content">Hello, Templates!</amp-fit-text></h1>
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
