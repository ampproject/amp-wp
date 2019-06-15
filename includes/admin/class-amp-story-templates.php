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
	const STORY_TEMPLATES_VERSION = '0.3.3';

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
		if ( ! post_type_exists( AMP_Story_Post_Type::POST_TYPE_SLUG ) ) {
			return;
		}

		add_filter( 'rest_wp_block_query', array( $this, 'filter_rest_wp_block_query' ), 10, 2 );
		add_action( 'save_post_wp_block', array( $this, 'flag_template_as_modified' ) );

		// Temporary filters for disallowing the users to edit any templates until the feature has been implemented.
		add_filter( 'user_has_cap', array( $this, 'filter_user_has_cap' ), 10, 3 );
		add_filter( 'pre_get_posts', array( $this, 'filter_pre_get_posts' ) );

		$this->register_taxonomy();
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
				unset( $allcaps['edit_others_posts'] );
				unset( $allcaps['edit_published_posts'] );
			}
		}
		return $allcaps;
	}

	/**
	 * Temporarily filter pre_get_posts to not display templates in the list of reusable blocks.
	 *
	 * @param object $query WP_Query object.
	 * @return object WP Query modified object.
	 */
	public function filter_pre_get_posts( $query ) {
		global $pagenow;

		if ( 'edit.php' !== $pagenow || ! $query->is_admin ) {
			return $query;
		}

		if ( 'wp_block' !== $query->get( 'post_type' ) ) {
			return $query;
		}

		$tax_query = $query->get( 'tax_query' );
		if ( empty( $tax_query ) ) {
			$tax_query = array();
		}

		$tax_query[] = array(
			'taxonomy' => self::TEMPLATES_TAXONOMY,
			'field'    => 'slug',
			'terms'    => array( self::TEMPLATES_TERM ),
			'operator' => 'NOT IN',
		);

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

			$post_content      = str_replace( 'AMP_STORY_TEMPLATES_URL', amp_get_asset_url( 'images/story-templates' ), $post_content );
			$existing_template = $this->template_exists( $template['name'] );
			if ( $existing_template > 0 ) {
				$this->maybe_update_template( $existing_template, $template, $post_content );
				continue;
			}

			$post_id = wp_insert_post(
				wp_slash(
					array(
						'post_title'   => $template['title'],
						'post_type'    => 'wp_block',
						'post_status'  => 'publish',
						'post_content' => $post_content,
						'post_name'    => $template['name'],
					)
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

		remove_action( 'save_post_wp_block', array( $this, 'flag_template_as_modified' ) );
		wp_update_post(
			wp_slash(
				array(
					'ID'           => $template_id,
					'post_content' => $content,
					'post_title'   => $template['title'],
				)
			)
		);
		add_action( 'save_post_wp_block', array( $this, 'flag_template_as_modified' ) );
	}

	/**
	 * Get the static list of templates, including temlate name and content.
	 *
	 * @return array Story templates.
	 */
	public static function get_story_templates() {
		return array(
			array(
				'title' => __( 'Template: Travel Tip', 'amp' ),
				'name'  => 'travel-tip',
			),
			array(
				'title' => __( 'Template: Quote', 'amp' ),
				'name'  => 'quote',
			),
			array(
				'title' => __( 'Template: Travel CTA', 'amp' ),
				'name'  => 'travel-cta',
			),
			array(
				'title' => __( 'Template: Title Page', 'amp' ),
				'name'  => 'title-page',
			),
			array(
				'title' => __( 'Template: Travel Vertical', 'amp' ),
				'name'  => 'travel-vertical',
			),
			array(
				'title' => __( 'Template: Fandom Title', 'amp' ),
				'name'  => 'fandom-title',
			),
			array(
				'title' => __( 'Template: Fandom CTA', 'amp' ),
				'name'  => 'fandom-cta',
			),
			array(
				'title' => __( 'Template: Fandom Fact', 'amp' ),
				'name'  => 'fandom-fact',
			),
			array(
				'title' => __( 'Template: Fandom Fact Text', 'amp' ),
				'name'  => 'fandom-fact-text',
			),
			array(
				'title' => __( 'Template: Fandom Intro', 'amp' ),
				'name'  => 'fandom-intro',
			),
		);
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

		$args      = array(
			'name'           => $slug,
			'post_type'      => 'wp_block',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
		);
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
