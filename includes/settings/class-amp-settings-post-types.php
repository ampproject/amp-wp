<?php
/**
 * Post types settings.
 *
 * @package AMP
 */

/**
 * Settings post types class.
 */
class AMP_Settings_Post_Types {

	/**
	 * Settings section id.
	 *
	 * @var string
	 */
	protected $section_id = 'post_types';

	/**
	 * Settings config.
	 *
	 * @var array
	 */
	protected $setting = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->setting = array(
			'id'          => 'post_types_support',
			'label'       => __( 'Post Types Support', 'amp' ),
			'description' => __( 'Enable/disable AMP post type(s) support', 'amp' ),
		);
	}

	/**
	 * Initialize.
	 */
	public function init() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'update_option_' . AMP_Settings::SETTINGS_KEY, 'flush_rewrite_rules' );
		add_action( 'amp_settings_screen', array( $this, 'errors' ) );
	}

	/**
	 * Register the current page settings.
	 */
	public function register_settings() {
		register_setting(
			AMP_Settings::SETTINGS_KEY,
			AMP_Settings::SETTINGS_KEY,
			array( $this, 'validate' )
		);
		add_settings_section(
			$this->section_id,
			false,
			'__return_false',
			AMP_Settings::MENU_SLUG
		);
		add_settings_field(
			$this->setting['id'],
			$this->setting['label'],
			array( $this, 'render' ),
			AMP_Settings::MENU_SLUG,
			$this->section_id
		);
	}

	/**
	 * Getter for settings value.
	 *
	 * @param string $post_type The post type name.
	 * @return bool|array Return true if the post type is always on; the setting value otherwise.
	 */
	public function get_settings( $post_type = false ) {
		$settings = $this->validate( get_option( AMP_Settings::SETTINGS_KEY, array() ) );

		if ( false !== $post_type ) {
			if ( isset( $settings[ $this->setting['id'] ][ $post_type ] ) ) {
				return $settings[ $this->setting['id'] ][ $post_type ];
			}

			return false;
		}

		if ( empty( $settings[ $this->setting['id'] ] ) || ! is_array( $settings[ $this->setting['id'] ] ) ) {
			return array();
		}

		return $settings[ $this->setting['id'] ];
	}

	/**
	 * Getter for the supported post types.
	 *
	 * @return object Supported post types list.
	 */
	public function get_supported_post_types() {
		$core = get_post_types( array(
			'name' => 'post',
		), 'objects' );
		$cpt  = get_post_types( array(
			'public'   => true,
			'_builtin' => false,
		), 'objects' );

		return $core + $cpt;
	}

	/**
	 * Getter for the setting HTML input name attribute.
	 *
	 * @param string $post_type The post type name.
	 * @return object The setting HTML input name attribute.
	 */
	public function get_name_attribute( $post_type ) {
		$id = $this->setting['id'];
		return AMP_Settings::SETTINGS_KEY . "[{$id}][{$post_type}]";
	}

	/**
	 * Check whether the post type should be disabled or not.
	 *
	 * Since we can't flag a post type which is not enabled by setting and removed by plugin/theme,
	 * we can't disable the checkbox but the errors() takes care of this scenario.
	 *
	 * @param string $post_type The post type name.
	 * @return bool True if disabled; false otherwise.
	 */
	public function disabled( $post_type ) {
		$settings = $this->get_settings();

		// Disable if post type support was not added by setting and added by plugin/theme.
		if ( post_type_supports( $post_type, AMP_QUERY_VAR ) && ! isset( $settings[ $post_type ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Handle errors.
	 */
	public function errors() {
		$settings = $this->get_settings();

		foreach ( $settings as $post_type => $value ) {
			// Throw error if post type support was added by setting and removed by plugin/theme.
			if ( true === $value && ! post_type_supports( $post_type, AMP_QUERY_VAR ) ) {
				$post_type_object = get_post_type_object( $post_type );

				add_settings_error(
					$post_type,
					$post_type,
					sprintf(
						/* Translators: %s: Post type name. */
						__( '"%s" could not be activated because support was removed by a plugin or theme', 'amp' ),
						isset( $post_type_object->label ) ? $post_type_object->label : $post_type
					)
				);
			}
		}
	}

	/**
	 * Validate and sanitize the settings.
	 *
	 * @param array $settings The post types settings.
	 * @return array The post types settings.
	 */
	public function validate( $settings ) {
		if ( ! isset( $settings[ $this->setting['id'] ] ) || ! is_array( $settings[ $this->setting['id'] ] ) ) {
			return array();
		}

		foreach ( $settings[ $this->setting['id'] ] as $key => $value ) {
			$settings[ $this->setting['id'] ][ $key ] = (bool) $value;
		}

		return $settings;
	}

	/**
	 * Setting renderer.
	 */
	public function render() {
		require_once AMP__DIR__ . '/templates/admin/settings/fields/checkbox-post-types.php';
	}

	/**
	 * Get the instance of AMP_Settings_Post_Types.
	 *
	 * @return object $instance AMP_Settings_Post_Types instance.
	 */
	public static function get_instance() {
		static $instance;

		if ( ! $instance instanceof AMP_Settings_Post_Types ) {
			$instance = new AMP_Settings_Post_Types();
		}

		return $instance;
	}

}
