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
	 * Current version of the templates.
	 *
	 * @var string
	 */
	const STORY_TEMPLATES_VERSION = '0.1';

	/**
	 * Slug for templates' taxonomy.
	 */
	const TEMPLATES_TAXONOMY = 'amp_template';

	/**
	 * Slug for templates term.
	 */
	const TEMPLATES_TERM = 'story-template';

	/**
	 * Init.
	 */
	public function init() {
		if ( ! post_type_exists( 'amp_story' ) ) {
			return;
		}

		add_filter( 'rest_wp_block_query', array( $this, 'filter_rest_wp_block_query' ), 10, 2 );

		$this->register_taxonomy();
		$this->maybe_import_story_templates();
	}

	/**
	 * Import story templates if they haven't been imported previously.
	 */
	public function maybe_import_story_templates() {
		if ( self::STORY_TEMPLATES_VERSION === AMP_Options_Manager::get_option( 'story_templates_version' ) ) {
			return;
		}
		$this->import_story_templates();
		// @todo This is commented out for testing purposes until the PR gets ready.
		// AMP_Options_Manager::update_option( 'story_templates_version', self::STORY_TEMPLATES_VERSION );
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
			$post_id = wp_insert_post(
				array(
					'post_title'   => $template['title'],
					'post_type'    => 'wp_block',
					'post_status'  => 'publish',
					'post_content' => $template['content'],
				)
			);
			wp_set_object_terms( $post_id, self::TEMPLATES_TERM, self::TEMPLATES_TAXONOMY );
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
				"SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_content = %s AND post_status = 'publish'",
				array( $post_title, $post_content )
			)
		);
	}

	/**
	 * Register taxonomy for differentiating AMP Story Templates.
	 */
	private function register_taxonomy() {
		register_taxonomy(
			self::TEMPLATES_TAXONOMY,
			'wp_block',
			array(
				'query_var'             => self::TEMPLATES_TAXONOMY,
				'show_admin_column'     => false,
				'show_in_rest'          => true,
				'rest_base'             => self::TEMPLATES_TAXONOMY . 's',
				'rest_controller_class' => 'WP_REST_Terms_Controller',
				'public'                => false,
				'show_ui'               => false,
				'show_tagcloud'         => false,
				'show_in_quick_edit'    => false,
				'hierarchical'          => false,
				'show_in_menu'          => false,
				'meta_box_cb'           => false,
			)
		);

		if ( ! term_exists( self::TEMPLATES_TERM, self::TEMPLATES_TAXONOMY ) ) {
			wp_insert_term(
				__( 'Story Template', 'amp' ),
				self::TEMPLATES_TAXONOMY,
				array(
					'description' => __( 'Story Template', 'amp' ),
					'slug'        => self::TEMPLATES_TERM,
				)
			);
		}
	}

	/**
	 * Filter REST request for reusable blocks to not display templates under Reusable Blocks within other posts.
	 *
	 * @param array           $args Original args.
	 * @param WP_REST_Request $request WP REST Request object.
	 * @return array Args.
	 */
	public function filter_rest_wp_block_query( $args, $request ) {
		$headers = $request->get_headers();
		if ( ! isset( $headers['referer'][0] ) ) {
			return $args;
		}

		$parts = wp_parse_url( $headers['referer'][0] );
		if ( ! isset( $parts['query'] ) ) {
			return $args;
		}
		parse_str( $parts['query'], $params );
		if ( ! isset( $params['post'] ) || ! isset( $params['action'] ) ) {
			return $args;
		}

		$edited_post = get_post( absint( $params['post'] ) );
		if ( AMP_Story_Post_Type::POST_TYPE_SLUG !== $edited_post->post_type ) {
			if ( ! isset( $args['tax_query'] ) ) {
				$args['tax_query'] = array();
			}
			$args['tax_query'][] = array(
				'taxonomy' => self::TEMPLATES_TAXONOMY,
				'field'    => 'slug',
				'terms'    => array( self::TEMPLATES_TERM ),
				'operator' => 'NOT IN',
			);
		}

		return $args;
	}
}
