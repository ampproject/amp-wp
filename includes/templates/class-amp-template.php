<?php

class AMP_Template {
	const SITE_ICON_SIZE = 32;
	const CONTENT_MAX_WIDTH = 600;

	// Needed for 0.3 back-compat
	const DEFAULT_NAVBAR_BACKGROUND = '#0a89c0';
	const DEFAULT_NAVBAR_COLOR = '#fff';
	
	protected $content_max_width;
	protected $template_dir;
	protected $amp_base_template_data;
	protected $data;

	public function __construct() {
		$this->content_max_width = self::CONTENT_MAX_WIDTH;
		if ( isset( $GLOBALS['content_width'] ) && $GLOBALS['content_width'] > 0 ) {
			$content_max_width = $GLOBALS['content_width'];
		}
		$this->content_max_width = apply_filters( 'amp_content_max_width', $content_max_width );

		// Keep two filter hooks for backward compatibility (prefer the more general second option)
		$this->template_dir = apply_filters( 'amp_post_template_dir', AMP__DIR__ . '/theme-templates' );
		$this->template_dir = apply_filters( 'amp_template_dir', AMP__DIR__ . '/theme-templates' );

		$this->init_amp_template_data();
	}

	private function init_amp_template_data() {
		$this->amp_base_template_data = array(
			'document_title' => function_exists( 'wp_get_document_title' ) ? wp_get_document_title() : wp_title( '', false ), // back-compat with 4.3
			'html_tag_attributes' => array(),
			'site_icon_url' => apply_filters( 'amp_site_icon_url', function_exists( 'get_site_icon_url' ) ? get_site_icon_url( self::SITE_ICON_SIZE ) : '' ),
			'placeholder_image_url' => amp_get_asset_url( 'images/placeholder-icon.png' ),
			'content_max_width' => $this->content_max_width,
			'amp_runtime_script' => 'https://cdn.ampproject.org/v0.js',
			'amp_styles' => array(),
			'amp_component_scripts' => array(),
			'font_urls' => array(
				'merriweather' => 'https://fonts.googleapis.com/css?family=Merriweather:400,400italic,700,700italic',
			),
			'customizer_settings' => array(),
			'home_url' => home_url(),
			'blog_name' => get_bloginfo( 'name' ),
			'body_class' => '',
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
		);
	}

	public function get( $property, $default = null ) {
		if ( isset( $this->data[ $property ] ) ) {
			return $this->data[ $property ];
		} else {
			_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'Called for non-existant key ("%s").', 'amp' ), esc_html( $property ) ), '0.1' );
		}
		
		return $default;
	}

	public function get_customizer_setting( $name, $default = null ) {
		$settings = $this->get( 'customizer_settings' );
		if ( ! empty( $settings[ $name ] ) ) {
			return $settings[ $name ];
		}
		
		return $default;
	}

	protected function get_template_path( $template ) {
		return sprintf( '%s/%s.php', $this->template_dir, $template );
	}

	protected function load_parts( $templates ) {
		foreach ( $templates as $template ) {
			$file = $this->get_template_path( $template );
			$this->verify_and_include( $file, $template );
		}
	}

	private function verify_and_include( $file, $template_type ) {
		$located_file = $this->locate_template( $file );
		if ( $located_file ) {
			$file = $located_file;
		}
		if ( get_class( $this ) === 'AMP_Post_Template' ) {
			$file = apply_filters( 'amp_post_template_file', $file, $template_type, $this->post );
		}
		$file = apply_filters( 'amp_template_file', $file, $template_type );

		if ( ! $this->is_valid_template( $file ) ) {
			_doing_it_wrong( __METHOD__, sprintf( esc_html__( 'Path validation for template (%s) failed. Path cannot traverse and must be located in `%s`.', 'amp' ), esc_html( $file ), 'WP_CONTENT_DIR' ), '0.1' );
			return;
		}
		// Keeping two action hooks for backward compatibility
		// Prefer the more general amp_template_include_
		if ( get_class( $this ) === 'AMP_Post_Template' ) {
			do_action ( 'amp_post_template_include_' . $template_type, $this );
		}
		do_action( 'amp_template_include_' . $template_type, $this );

		include( $file );
	}

	protected function locate_template( $file ) {
		$search_file = sprintf( 'amp/%s', basename( $file ) );
		return locate_template( array( $search_file ), false );
	}

	protected function is_valid_template( $template ) {
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

	protected function add_data( $data ) {
		$this->data = array_merge( $this->data, $data );
	}

	protected function add_data_by_key( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	protected function merge_data_for_key( $key, $value ) {
		if ( is_array( $this->data[ $key ] ) ) {
			$this->data[ $key ] = array_merge( $this->data[ $key ], $value );
		} else {
			$this->add_data_by_key( $key, $value );
		}
	}

	protected function load( $template ) {
		$this->load_parts( array( $template ) );
	}
}