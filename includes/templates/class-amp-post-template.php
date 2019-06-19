<?php
/**
 * AMP_Post_Template class.
 *
 * @package AMP
 */

/**
 * Class AMP_Post_Template
 *
 * @since 0.2
 */
class AMP_Post_Template {

	/**
	 * Site icon size.
	 *
	 * @since 0.2
	 * @var int
	 */
	const SITE_ICON_SIZE = 32;

	/**
	 * Content max width.
	 *
	 * @since 0.4
	 * @var int
	 */
	const CONTENT_MAX_WIDTH = 600;

	/**
	 * Default navbar background.
	 *
	 * Needed for 0.3 back-compat
	 *
	 * @since 0.4
	 * @var string
	 */
	const DEFAULT_NAVBAR_BACKGROUND = '#0a89c0';

	/**
	 * Default navbar color.
	 *
	 * Needed for 0.3 back-compat
	 *
	 * @since 0.4
	 * @var string
	 */
	const DEFAULT_NAVBAR_COLOR = '#fff';

	/**
	 * Template directory.
	 *
	 * @since 0.2
	 * @var string
	 */
	private $template_dir;

	/**
	 * Post template data.
	 *
	 * @since 0.2
	 * @var array
	 */
	private $data;

	/**
	 * Post ID.
	 *
	 * @since 0.2
	 * @var int
	 */
	public $ID;

	/**
	 * Post.
	 *
	 * @since 0.2
	 * @var WP_Post
	 */
	public $post;

	/**
	 * AMP_Post_Template constructor.
	 *
	 * @param WP_Post|int $post Post.
	 */
	public function __construct( $post ) {

		$this->template_dir = apply_filters( 'amp_post_template_dir', AMP__DIR__ . '/templates' );

		if ( $post instanceof WP_Post ) {
			$this->post = $post;
		} else {
			$this->post = get_post( $post );
		}

		// Make sure we have a post, or bail if not.
		if ( is_a( $this->post, 'WP_Post' ) ) {
			$this->ID = $this->post->ID;
		} else {
			return;
		}

		$content_max_width = self::CONTENT_MAX_WIDTH;
		if ( isset( $GLOBALS['content_width'] ) && $GLOBALS['content_width'] > 0 ) {
			$content_max_width = $GLOBALS['content_width'];
		}
		$content_max_width = apply_filters( 'amp_content_max_width', $content_max_width );

		$this->data = array(
			'content_max_width'     => $content_max_width,

			'document_title'        => function_exists( 'wp_get_document_title' ) ? wp_get_document_title() : wp_title( '', false ), // Back-compat with 4.3.
			'canonical_url'         => get_permalink( $this->ID ),
			'home_url'              => home_url( '/' ),
			'blog_name'             => get_bloginfo( 'name' ),

			'html_tag_attributes'   => array(),
			'body_class'            => '',

			'site_icon_url'         => apply_filters( 'amp_site_icon_url', function_exists( 'get_site_icon_url' ) ? get_site_icon_url( self::SITE_ICON_SIZE ) : '' ),
			'placeholder_image_url' => amp_get_asset_url( 'images/placeholder-icon.png' ),

			'featured_image'        => false,
			'comments_link_url'     => false,
			'comments_link_text'    => false,

			'amp_runtime_script'    => 'https://cdn.ampproject.org/v0.js',
			'amp_component_scripts' => array(),

			'customizer_settings'   => array(),

			'font_urls'             => array(),

			'post_amp_stylesheets'  => array(),
			'post_amp_styles'       => array(), // Deprecated.

			'amp_analytics'         => amp_add_custom_analytics(),
		);

		$this->build_post_content();
		$this->build_post_data();
		$this->build_customizer_settings();
		$this->build_html_tag_attributes();

		/**
		 * Filters AMP template data.
		 *
		 * @since 0.2
		 *
		 * @param array   $data Template data.
		 * @param WP_Post $post Post.
		 */
		$this->data = apply_filters( 'amp_post_template_data', $this->data, $this->post );
	}

	/**
	 * Getter.
	 *
	 * @param string $property Property name.
	 * @param mixed  $default  Default value.
	 *
	 * @return mixed Value.
	 */
	public function get( $property, $default = null ) {
		if ( isset( $this->data[ $property ] ) ) {
			return $this->data[ $property ];
		} else {
			/* translators: %s is key name */
			_doing_it_wrong( __METHOD__, esc_html( sprintf( __( 'Called for non-existent key ("%s").', 'amp' ), $property ) ), '0.1' );
		}

		return $default;
	}

	/**
	 * Get customizer setting.
	 *
	 * @param string $name    Name.
	 * @param mixed  $default Default value.
	 * @return mixed value.
	 */
	public function get_customizer_setting( $name, $default = null ) {
		$settings = $this->get( 'customizer_settings' );
		if ( ! empty( $settings[ $name ] ) ) {
			return $settings[ $name ];
		}

		return $default;
	}

	/**
	 * Load and print the template parts for the given post.
	 */
	public function load() {
		global $wp_query;
		$template = is_page() || $wp_query->is_posts_page ? 'page' : 'single';
		$this->load_parts( array( $template ) );
	}

	/**
	 * Load template parts.
	 *
	 * @param string[] $templates Templates.
	 */
	public function load_parts( $templates ) {
		foreach ( $templates as $template ) {
			$file = $this->get_template_path( $template );
			$this->verify_and_include( $file, $template );
		}
	}

	/**
	 * Get template path.
	 *
	 * @param string $template Template name.
	 * @return string Template path.
	 */
	private function get_template_path( $template ) {
		return sprintf( '%s/%s.php', $this->template_dir, $template );
	}

	/**
	 * Add data.
	 *
	 * @param array $data Data.
	 */
	private function add_data( $data ) {
		$this->data = array_merge( $this->data, $data );
	}

	/**
	 * Add data by key.
	 *
	 * @param string $key   Key.
	 * @param mixed  $value Value.
	 */
	private function add_data_by_key( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * Merge data for key.
	 *
	 * @param string $key   Key.
	 * @param mixed  $value Value.
	 */
	private function merge_data_for_key( $key, $value ) {
		if ( is_array( $this->data[ $key ] ) ) {
			$this->data[ $key ] = array_merge( $this->data[ $key ], $value );
		} else {
			$this->add_data_by_key( $key, $value );
		}
	}

	/**
	 * Build post data.
	 *
	 * @since 0.2
	 */
	private function build_post_data() {
		$post_title              = get_the_title( $this->ID );
		$post_publish_timestamp  = get_the_date( 'U', $this->ID );
		$post_modified_timestamp = get_post_modified_time( 'U', false, $this->post );
		$post_author             = get_userdata( $this->post->post_author );

		$data = array(
			'post'                     => $this->post,
			'post_id'                  => $this->ID,
			'post_title'               => $post_title,
			'post_publish_timestamp'   => $post_publish_timestamp,
			'post_modified_timestamp'  => $post_modified_timestamp,
			'post_author'              => $post_author,
			'post_canonical_link_url'  => '',
			'post_canonical_link_text' => '',
		);

		$customizer_settings = AMP_Customizer_Settings::get_settings();
		if ( ! empty( $customizer_settings['display_exit_link'] ) ) {
			$data['post_canonical_link_url']  = get_permalink( $this->ID );
			$data['post_canonical_link_text'] = __( 'Exit Reader Mode', 'amp' );
		}

		$this->add_data( $data );

		$this->build_post_featured_image();
		$this->build_post_comments_data();
	}

	/**
	 * Buuild post comments data.
	 */
	private function build_post_comments_data() {
		if ( ! post_type_supports( $this->post->post_type, 'comments' ) ) {
			return;
		}

		$comments_open = comments_open( $this->ID );

		// Don't show link if close and no comments.
		if ( ! $comments_open
			&& ! $this->post->comment_count ) {
			return;
		}

		$comments_link_url  = get_comments_link( $this->ID );
		$comments_link_text = $comments_open
			? __( 'Leave a Comment', 'amp' )
			: __( 'View Comments', 'amp' );

		$this->add_data(
			array(
				'comments_link_url'  => $comments_link_url,
				'comments_link_text' => $comments_link_text,
			)
		);
	}

	/**
	 * Build post content.
	 */
	private function build_post_content() {
		$amp_content = new AMP_Content(
			$this->post->post_content,
			amp_get_content_embed_handlers( $this->post ),
			amp_get_content_sanitizers( $this->post ),
			array(
				'content_max_width' => $this->get( 'content_max_width' ),
			)
		);

		$this->add_data_by_key( 'post_amp_content', $amp_content->get_amp_content() );
		$this->merge_data_for_key( 'amp_component_scripts', $amp_content->get_amp_scripts() );
		$this->add_data_by_key( 'post_amp_stylesheets', $amp_content->get_amp_stylesheets() );
	}

	/**
	 * Build post featured image.
	 */
	private function build_post_featured_image() {
		$post_id       = $this->ID;
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

		$dom    = AMP_DOM_Utils::get_dom_from_content( $featured_html );
		$assets = AMP_Content_Sanitizer::sanitize_document(
			$dom,
			amp_get_content_sanitizers( $this->post ),
			array(
				'content_max_width' => $this->get( 'content_max_width' ),
			)
		);

		$sanitized_html = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->add_data_by_key(
			'featured_image',
			array(
				'amp_html' => $sanitized_html,
				'caption'  => $featured_image->post_excerpt,
			)
		);

		if ( $assets['scripts'] ) {
			$this->merge_data_for_key( 'amp_component_scripts', $assets['scripts'] );
		}

		if ( $assets['stylesheets'] ) {
			$this->merge_data_for_key( 'post_amp_stylesheets', $assets['stylesheets'] );
		}
	}

	/**
	 * Build customizer settings.
	 */
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
	 * Build HTML tag attributes.
	 */
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

	/**
	 * Verify and include.
	 *
	 * @param string $file          File.
	 * @param string $template_type Template type.
	 */
	private function verify_and_include( $file, $template_type ) {
		$located_file = $this->locate_template( $file );
		if ( $located_file ) {
			$file = $located_file;
		}

		$file = apply_filters( 'amp_post_template_file', $file, $template_type, $this->post );
		if ( ! $this->is_valid_template( $file ) ) {
			/* translators: 1: the template file, 2: WP_CONTENT_DIR. */
			_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'Path validation for template (%1$s) failed. Path cannot traverse and must be located in `%2$s`.', 'amp' ), esc_html( $file ), 'WP_CONTENT_DIR' ), '0.1' );
			return;
		}

		do_action( 'amp_post_template_include_' . $template_type, $this );
		include $file;
	}

	/**
	 * Locate template.
	 *
	 * @param string $file File.
	 * @return string The template filename if one is located.
	 */
	private function locate_template( $file ) {
		$search_file = sprintf( 'amp/%s', basename( $file ) );
		return locate_template( array( $search_file ), false );
	}

	/**
	 * Is valid template.
	 *
	 * @param string $template Template name.
	 * @return bool Whether valid.
	 */
	private function is_valid_template( $template ) {
		if ( false !== strpos( $template, '..' ) ) {
			return false;
		}

		if ( false !== strpos( $template, './' ) ) {
			return false;
		}

		if ( ! file_exists( $template ) ) {
			return false;
		}

		return true;
	}
}
