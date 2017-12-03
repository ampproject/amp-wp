<?php
/**
 * Post types settings.
 *
 * @package AMP
 * @since 0.6
 */

/**
 * Settings post types class.
 *
 * @since 0.6
 */
class AMP_Settings_Post_Types {

	/**
	 * Settings section id.
	 *
	 * @since 0.6
	 * @var string
	 */
	protected $section_id = 'post_types';

	/**
	 * Settings config.
	 *
	 * @since 0.6
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
	 * @since 0.6
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
	 * @since 0.6
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

		return array_merge( $core, $cpt );
	}

	/**
	 * Getter for the setting HTML input name attribute.
	 *
	 * @since 0.6
	 * @param string $post_type The post type name.
	 * @return object The setting HTML input name attribute.
	 */
	public function get_name_attribute( $post_type ) {
		$id = $this->setting['id'];
		return AMP_Settings::SETTINGS_KEY . "[{$id}][{$post_type}]";
	}

	/**
	 * Handle errors.
	 *
	 * @since 0.6
	 */
	public function errors() {
		$on_update = (
			isset( $_GET['settings-updated'] ) // WPCS: CSRF ok.
			&&
			true === (bool) wp_unslash( $_GET['settings-updated'] ) // WPCS: CSRF ok.
		);

		// Only apply on update.
		if ( ! $on_update ) {
			return;
		}

		foreach ( $this->get_supported_post_types() as $post_type ) {
			if ( ! isset( $post_type->name, $post_type->label ) ) {
				continue;
			}

			$post_type_support = post_type_supports( $post_type->name, AMP_QUERY_VAR );
			$value             = $this->get_settings( $post_type->name );

			if ( true === $value && true !== $post_type_support ) {
				/* Translators: %s: Post type name. */
				$error = __( '"%s" could not be activated because support is removed by a plugin or theme', 'amp' );
			} elseif ( empty( $value ) && true === $post_type_support ) {
				/* Translators: %s: Post type name. */
				$error = __( '"%s" could not be deactivated because support is added by a plugin or theme', 'amp' );
			}

			if ( isset( $error ) ) {
				add_settings_error(
					$post_type->name,
					$post_type->name,
					sprintf(
						$error,
						$post_type->label
					)
				);
			}
		}
	}

	/**
	 * Validate and sanitize the settings.
	 *
	 * @since 0.6
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
	 *
	 * @since 0.6
	 */
	public function render() {
		require_once AMP__DIR__ . '/templates/admin/settings/fields/checkbox-post-types.php';
	}

	/**
	 * Get the instance of AMP_Settings_Post_Types.
	 *
	 * @since 0.6
	 * @return object $instance AMP_Settings_Post_Types instance.
	 */
	public static function get_instance() {
		static $instance;

		if ( ! ( $instance instanceof AMP_Settings_Post_Types ) ) {
			$instance = new AMP_Settings_Post_Types();
		}

		return $instance;
	}

}
