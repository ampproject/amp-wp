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
	const STORY_TEMPLATES_VERSION = '0.3.8';

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
		// Always hide the story templates.
		add_filter( 'pre_get_posts', [ $this, 'filter_pre_get_posts' ] );

		// Temporary filters for disallowing the users to edit any templates until the feature has been implemented.
		add_filter( 'user_has_cap', [ $this, 'filter_user_has_cap' ], 10, 3 );

		// We need to register the taxonomy even if AMP Stories is disabled for tax_query.
		$this->register_taxonomy();

		if ( ! AMP_Options_Manager::is_stories_experience_enabled() ) {
			return;
		}

		add_action( 'save_post_wp_block', [ $this, 'flag_template_as_modified' ] );

		$this->maybe_import_story_templates();
	}

	/**
	 * Temporarily hide editing option for Templates.
	 *
	 * @param  array  $allcaps Existing capabilities for the user.
	 * @param  string $caps    Capabilities provided by map_meta_cap().
	 * @param  array  $args    Arguments for current_user_can().
	 * @return array Modified capabilities.
	 */
	public function filter_user_has_cap( $allcaps, $caps, $args ) {
		if ( 'edit_post' === $args[0] && isset( $args[2] ) ) {
			if ( has_term( self::TEMPLATES_TERM, self::TEMPLATES_TAXONOMY, $args[2] ) ) {
				unset( $allcaps['edit_others_posts'], $allcaps['edit_published_posts'] );
			}
		}
		return $allcaps;
	}

	/**
	 * Ensures that the templates only display in the AMP Story editor.
	 *
	 * This filters pre_get_posts to not display templates in the list of reusable blocks,
	 * or anywhere other than in the AMP Story editor.
	 *
	 * @param object $query WP_Query object.
	 * @return object WP Query modified object.
	 */
	public function filter_pre_get_posts( $query ) {
		if ( 'wp_block' !== $query->get( 'post_type' ) ) {
			return $query;
		}

		$referer = wp_parse_url( wp_get_referer() );

		$is_story_page = false;
		if ( isset( $referer['query'] ) ) {
			$parsed_args = wp_parse_args( $referer['query'] );

			if ( isset( $parsed_args['post_type'] ) && AMP_Story_Post_Type::POST_TYPE_SLUG === $parsed_args['post_type'] ) {
				$is_story_page = true; // This is in the editor for a new AMP Story.
			}

			if ( isset( $parsed_args['post'] ) && AMP_Story_Post_Type::POST_TYPE_SLUG === get_post_type( $parsed_args['post'] ) ) {
				$is_story_page = true; // This is in the editor for an existing AMP Story.
			}
		}

		$tax_query = $query->get( 'tax_query' );
		if ( empty( $tax_query ) ) {
			$tax_query = [];
		}

		$reusable_query = [
			'taxonomy' => self::TEMPLATES_TAXONOMY,
			'field'    => 'slug',
			'terms'    => [ self::TEMPLATES_TERM ],
			'operator' => $is_story_page ? 'IN' : 'NOT IN', // Include templates if is Story page, exclude otherwise.
		];

		$tax_query[] = $reusable_query;
		$query->set( 'tax_query', $tax_query );
		return $query;
	}

	/**
	 * Import story templates if they haven't been imported previously.
	 */
	private function maybe_import_story_templates() {
		if ( self::STORY_TEMPLATES_VERSION === AMP_Options_Manager::get_option( 'story_templates_version' ) ) {
			return;
		}
		AMP_Options_Manager::update_option( 'story_templates_version', self::STORY_TEMPLATES_VERSION );
		$this->import_story_templates();
	}

	/**
	 * Import logic for story templates.
	 */
	public function import_story_templates() {
		$templates = self::get_story_templates();
		foreach ( $templates as $template ) {
			$post_content = @file_get_contents( AMP__DIR__ . '/includes/templates/story-templates/' . $template['name'] . '.html' ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPress.WP.AlternativeFunctions.file_system_read_file_get_contents, WordPress.XSS.EscapeOutput, WordPress.Security.EscapeOutput

			if ( empty( $post_content ) ) {
				continue;
			}

			$post_content      = str_replace( 'AMP_STORY_TEMPLATES_URL', amp_get_asset_url( 'images/stories-editor/story-templates' ), $post_content );
			$existing_template = $this->template_exists( $template['name'] );
			if ( $existing_template > 0 ) {
				$this->maybe_update_template( $existing_template, $template, $post_content );
				continue;
			}

			$post_id = wp_insert_post(
				wp_slash(
					[
						'post_title'   => $template['title'],
						'post_type'    => 'wp_block',
						'post_status'  => 'publish',
						'post_content' => $post_content,
						'post_name'    => $template['name'],
					]
				)
			);
			if ( ! $post_id ) {
				continue;
			}
			wp_set_object_terms( $post_id, self::TEMPLATES_TERM, self::TEMPLATES_TAXONOMY );
			add_post_meta( $post_id, 'amp_template_unmodified', true );
		}
	}

	/**
	 * Update the template if it hasn't been update by the user.
	 *
	 * @param integer $template_id Template ID.
	 * @param array   $template Template's data.
	 * @param string  $content Template's content.
	 */
	protected function maybe_update_template( $template_id, $template, $content ) {

		// If the template has been modified meanwhile, return.
		if ( false === (bool) get_post_meta( $template_id, 'amp_template_unmodified', true ) ) {
			return;
		}

		remove_action( 'save_post_wp_block', [ $this, 'flag_template_as_modified' ] );
		wp_update_post(
			wp_slash(
				[
					'ID'           => $template_id,
					'post_content' => $content,
					'post_title'   => $template['title'],
				]
			)
		);
		add_action( 'save_post_wp_block', [ $this, 'flag_template_as_modified' ] );
	}

	/**
	 * Get the static list of templates, including temlate name and content.
	 *
	 * @return array Story templates.
	 */
	public static function get_story_templates() {
		return [
			[
				'title' => __( 'Template: Travel Tip', 'amp' ),
				'name'  => 'travel-tip',
			],
			[
				'title' => __( 'Template: Quote', 'amp' ),
				'name'  => 'quote',
			],
			[
				'title' => __( 'Template: Travel CTA', 'amp' ),
				'name'  => 'travel-cta',
			],
			[
				'title' => __( 'Template: Title Page', 'amp' ),
				'name'  => 'title-page',
			],
			[
				'title' => __( 'Template: Travel Vertical', 'amp' ),
				'name'  => 'travel-vertical',
			],
			[
				'title' => __( 'Template: Fandom Title', 'amp' ),
				'name'  => 'fandom-title',
			],
			[
				'title' => __( 'Template: Fandom CTA', 'amp' ),
				'name'  => 'fandom-cta',
			],
			[
				'title' => __( 'Template: Fandom Fact', 'amp' ),
				'name'  => 'fandom-fact',
			],
			[
				'title' => __( 'Template: Fandom Fact Text', 'amp' ),
				'name'  => 'fandom-fact-text',
			],
			[
				'title' => __( 'Template: Fandom Intro', 'amp' ),
				'name'  => 'fandom-intro',
			],
		];
	}

	/**
	 * Determine if a post exists based on slug.
	 *
	 * @param string $slug Post slug.
	 * @return int Post ID if post exists, 0 otherwise.
	 */
	private function template_exists( $slug ) {
		if ( empty( $slug ) ) {
			return 0;
		}

		$args      = [
			'name'           => $slug,
			'post_type'      => 'wp_block',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
		];
		$templates = get_posts( $args );
		if ( ! empty( $templates ) ) {
			return $templates[0]->ID;
		}

		return 0;
	}

	/**
	 * Register taxonomy for differentiating AMP Story Templates.
	 */
	private function register_taxonomy() {
		register_taxonomy(
			self::TEMPLATES_TAXONOMY,
			'wp_block',
			[
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
			]
		);

		if ( ! term_exists( self::TEMPLATES_TERM, self::TEMPLATES_TAXONOMY ) ) {
			wp_insert_term(
				__( 'Story Template', 'amp' ),
				self::TEMPLATES_TAXONOMY,
				[
					'description' => __( 'Story Template', 'amp' ),
					'slug'        => self::TEMPLATES_TERM,
				]
			);
		}
	}

	/**
	 * Flag template as modified when it's being saved.
	 *
	 * @todo Confirm if tracking this is necessary, maybe the templates will be static.
	 * @param int $post_id Post ID.
	 */
	public function flag_template_as_modified( $post_id ) {
		if ( ! has_term( self::TEMPLATES_TERM, self::TEMPLATES_TAXONOMY, $post_id ) ) {
			return;
		}

		if ( true === (bool) get_post_meta( $post_id, 'amp_template_unmodified', true ) ) {
			update_post_meta( $post_id, 'amp_template_unmodified', false );
		}
	}
}
