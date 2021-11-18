<?php
/**
 * Class PluginSuppression.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AMP_Validation_Error_Taxonomy;
use AMP_Validation_Manager;
use AMP_Validated_URL_Post_Type;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\DevTools\CallbackReflection;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Block_Type_Registry;
use WP_Hook;
use WP_Term;
use WP_Widget;
use WP_User;

/**
 * Suppress plugins from running by removing their hooks and nullifying their shortcodes, widgets, and blocks.
 *
 * @package AmpProject\AmpWP
 * @internal
 * @since 2.0
 */
final class PluginSuppression implements Service, Registerable {

	/**
	 * Plugin registry to use.
	 *
	 * @var PluginRegistry
	 */
	private $plugin_registry;

	/**
	 * Callback reflector to use.
	 *
	 * @var CallbackReflection
	 */
	private $callback_reflection;

	/**
	 * Paired Routing.
	 *
	 * @var PairedRouting
	 */
	private $paired_routing;

	/**
	 * Original render callbacks for blocks.
	 *
	 * Populated via the `register_block_type_args` filter at the moment the block is first registered. This is useful
	 * to detect a suppressed plugin's blocks which had their `render_callback` wrapped by another function before
	 * plugin suppression is started at the `wp` action.
	 *
	 * @see gutenberg_current_parsed_block_tracking()
	 * @var array
	 */
	private $original_block_render_callbacks = [];

	/**
	 * Instantiate the plugin suppression service.
	 *
	 * @param PluginRegistry     $plugin_registry     Plugin registry to use.
	 * @param CallbackReflection $callback_reflection Callback reflector to use.
	 * @param PairedRouting      $paired_routing      Paired routing service to use.
	 */
	public function __construct( PluginRegistry $plugin_registry, CallbackReflection $callback_reflection, PairedRouting $paired_routing ) {
		$this->plugin_registry     = $plugin_registry;
		$this->callback_reflection = $callback_reflection;
		$this->paired_routing      = $paired_routing;
	}

	/**
	 * Register the service with the system.
	 */
	public function register() {
		add_filter( 'amp_default_options', [ $this, 'filter_default_options' ] );
		add_filter( 'amp_options_updating', [ $this, 'sanitize_options' ], 10, 2 );

		add_filter(
			'register_block_type_args',
			function ( $props, $block_name ) {
				if ( isset( $props['render_callback'] ) ) {
					$this->original_block_render_callbacks[ $block_name ] = $props['render_callback'];
				}
				return $props;
			},
			~PHP_INT_MAX,
			2
		);

		// Priority 8 needed to run before ReaderThemeLoader::override_theme() at priority 9.
		add_action( 'plugins_loaded', [ $this, 'initialize' ], 8 );
	}

	/**
	 * Initialize.
	 */
	public function initialize() {
		// When a Reader theme is selected and an AMP request is being made, start suppressing as early as possible.
		// This can be done because we know it is an AMP page due to the query parameter, but it also _has_ to be done
		// specifically for the case of accessing the AMP Customizer (in which customize.php is requested with the query
		// parameter) in order to prevent the registration of Customizer controls from suppressed plugins. Suppression
		// could be done early for Transitional mode as well since a query parameter is also used for frontend requests
		// but there is no similar need to suppress the registration of Customizer controls in Transitional mode since
		// there is no separate Customizer for AMP in Transitional mode (or legacy Reader mode).
		// @todo This check could be replaced with ( ! amp_is_canonical() && $this->paired_routing->has_endpoint() ).
		if ( $this->is_reader_theme_request() ) {
			$this->suppress_plugins();
		} else {
			$min_priority = defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : ~PHP_INT_MAX; // phpcs:ignore PHPCompatibility.Constants.NewConstants.php_int_minFound

			// In Standard mode we _have_ to wait for the wp action because with the absence of a query parameter
			// we have to rely on amp_is_request() and the WP_Query to determine whether a plugin should be suppressed.
			add_action( 'wp', [ $this, 'maybe_suppress_plugins' ], $min_priority );
		}
	}

	/**
	 * Is reader theme request.
	 *
	 * @return bool
	 */
	public function is_reader_theme_request() {
		return (
			AMP_Theme_Support::READER_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT )
			&&
			ReaderThemes::DEFAULT_READER_THEME !== AMP_Options_Manager::get_option( Option::READER_THEME )
			&&
			$this->paired_routing->has_endpoint()
		);
	}

	/**
	 * Add default option.
	 *
	 * @param array $defaults Default options.
	 * @return array Defaults.
	 */
	public function filter_default_options( $defaults ) {
		$defaults[ Option::SUPPRESSED_PLUGINS ] = [];
		return $defaults;
	}

	/**
	 * Suppress plugins if on an AMP endpoint.
	 *
	 * @return bool Whether plugins are being suppressed.
	 */
	public function maybe_suppress_plugins() {
		if ( amp_is_request() ) {
			return $this->suppress_plugins();
		}
		return false;
	}

	/**
	 * Suppress plugins.
	 *
	 * @return bool Whether plugins are being suppressed.
	 */
	public function suppress_plugins() {
		$suppressed = AMP_Options_Manager::get_option( Option::SUPPRESSED_PLUGINS );
		if ( empty( $suppressed ) ) {
			return false;
		}

		$suppressed_plugin_slugs = array_keys( $suppressed );

		$this->suppress_hooks( $suppressed_plugin_slugs );
		$this->suppress_shortcodes( $suppressed_plugin_slugs );
		$this->suppress_blocks( $suppressed_plugin_slugs );
		$this->suppress_widgets( $suppressed_plugin_slugs );

		return true;
	}

	/**
	 * Sanitize options.
	 *
	 * @param array $options     Existing options with already-sanitized values for updating.
	 * @param array $new_options Unsanitized options being submitted for updating.
	 *
	 * @return array Sanitized options.
	 */
	public function sanitize_options( $options, $new_options ) {
		if ( ! isset( $new_options[ Option::SUPPRESSED_PLUGINS ] ) ) {
			return $options;
		}

		$option                    = $options[ Option::SUPPRESSED_PLUGINS ];
		$posted_suppressed_plugins = $new_options[ Option::SUPPRESSED_PLUGINS ];

		$plugins = $this->plugin_registry->get_plugins( true );
		foreach ( $posted_suppressed_plugins as $plugin_slug => $suppressed ) {
			if ( ! isset( $plugins[ $plugin_slug ] ) ) {
				unset( $option[ $plugin_slug ] );
				continue;
			}

			$suppressed = rest_sanitize_boolean( $suppressed );
			if ( isset( $option[ $plugin_slug ] ) && ! $suppressed ) {

				// Remove the plugin from being suppressed.
				unset( $option[ $plugin_slug ] );
			} elseif ( ! isset( $option[ $plugin_slug ] ) && $suppressed && array_key_exists( $plugin_slug, $plugins ) ) {
				$user = wp_get_current_user();

				$option[ $plugin_slug ] = [
					// Note that we store the version that was suppressed so that we can alert the user when to check again.
					Option::SUPPRESSED_PLUGINS_LAST_VERSION => $plugins[ $plugin_slug ]['Version'],
					Option::SUPPRESSED_PLUGINS_TIMESTAMP => time(),
					Option::SUPPRESSED_PLUGINS_USERNAME  => $user instanceof WP_User ? $user->user_nicename : null,
				];
			}
		}

		$options[ Option::SUPPRESSED_PLUGINS ] = $option;

		return $options;
	}

	/**
	 * Provides validation errors for a plugin specified by slug.
	 *
	 * @param string $plugin_slug Plugin slug.
	 * @return array Validation errors.
	 */
	private function get_sorted_plugin_validation_errors( $plugin_slug ) {
		$errors_by_source = AMP_Validated_URL_Post_Type::get_recent_validation_errors_by_source();

		if ( ! isset( $errors_by_source['plugin'][ $plugin_slug ] ) ) {
			return [];
		}

		$validation_errors = $errors_by_source['plugin'][ $plugin_slug ];

		usort(
			$validation_errors,
			static function ( $a, $b ) {
				/** @var WP_Term */
				$a_term = $a['term'];

				/** @var WP_Term */
				$b_term = $b['term'];

				$a_reviewed = ( (int) $a_term->term_group & AMP_Validation_Error_Taxonomy::ACKNOWLEDGED_VALIDATION_ERROR_BIT_MASK );
				$b_reviewed = ( (int) $b_term->term_group & AMP_Validation_Error_Taxonomy::ACKNOWLEDGED_VALIDATION_ERROR_BIT_MASK );
				if ( $a_reviewed !== $b_reviewed ) {
					return (int) $a_reviewed - (int) $b_reviewed;
				}

				$a_removed = ( (int) $a_term->term_group & AMP_Validation_Error_Taxonomy::ACCEPTED_VALIDATION_ERROR_BIT_MASK );
				$b_removed = ( (int) $b_term->term_group & AMP_Validation_Error_Taxonomy::ACCEPTED_VALIDATION_ERROR_BIT_MASK );
				return (int) $a_removed - (int) $b_removed;
			}
		);

		// Because this data will be accessed via REST, add additional fields that are not easily rendered in JS.
		$validation_errors = array_map(
			static function( $validation_error ) {
				$term = $validation_error['term'];

				$validation_error['edit_url'] = admin_url(
					add_query_arg(
						[
							AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG => $term->name,
							'post_type' => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
						],
						'edit.php'
					)
				);

				$validation_error['title'] = AMP_Validation_Error_Taxonomy::get_error_title_from_code( $validation_error['data'] );

				$validation_error['is_removed']  = (bool) ( (int) $term->term_group & AMP_Validation_Error_Taxonomy::ACCEPTED_VALIDATION_ERROR_BIT_MASK );
				$validation_error['is_reviewed'] = (bool) ( (int) $term->term_group & AMP_Validation_Error_Taxonomy::ACKNOWLEDGED_VALIDATION_ERROR_BIT_MASK );
				$validation_error['tooltip']     = sprintf(
					/* translators: %1 is whether validation error is 'removed' or 'kept', %2 is whether validation error is 'reviewed' or 'unreviewed' */
					__( 'Invalid markup causing the validation error is %1$s and %2$s. See all validated URL(s) with this validation error.', 'amp' ),
					$validation_error['is_removed'] ? __( 'removed', 'amp' ) : __( 'kept', 'amp' ),
					$validation_error['is_reviewed'] ? __( 'reviewed', 'amp' ) : __( 'unreviewed', 'amp' )
				);

				return $validation_error;
			},
			$validation_errors
		);

		return $validation_errors;
	}

	/**
	 * Provides a keyed array of active plugins with keys being slugs and values being plugin info plus validation error details.
	 *
	 * Plugins are sorted by validation error count, in descending order.
	 *
	 * @return array Plugins.
	 */
	public function get_suppressible_plugins_with_details() {
		$plugins = [];
		foreach ( $this->plugin_registry->get_plugins( true ) as $slug => $plugin ) {
			$plugin['validation_errors'] = $this->get_sorted_plugin_validation_errors( $slug );
			$plugins[ $slug ]            = $plugin;
		}
		return $plugins;
	}

	/**
	 * Prepare suppressed plugins for response.
	 *
	 * Augment the suppressed plugins data with additional information.
	 *
	 * @param array $suppressed_plugins Suppressed plugins.
	 * @return array Prepared suppressed plugins.
	 */
	public function prepare_suppressed_plugins_for_response( $suppressed_plugins ) {
		return array_map(
			function ( $suppressed_plugin ) {
				if ( ! is_array( $suppressed_plugin ) ) {
					return $suppressed_plugin;
				}

				if ( isset( $suppressed_plugin['username'] ) ) {
					$username = $suppressed_plugin['username'];
					unset( $suppressed_plugin['username'] );
					$suppressed_plugin['user'] = $this->prepare_user_for_response( $username );
				}

				return $suppressed_plugin;
			},
			$suppressed_plugins
		);
	}

	/**
	 * Prepare user for response.
	 *
	 * @param string $username Username.
	 * @return array User data.
	 */
	private function prepare_user_for_response( $username ) {
		$response = [
			'slug' => $username,
		];

		$user = get_user_by( 'slug', $username );
		if ( $user ) {
			$response['name'] = $user->display_name;
		}

		return $response;
	}

	/**
	 * Suppress plugin hooks.
	 *
	 * @param string[] $suppressed_plugins Suppressed plugins.
	 * @global WP_Hook[] $wp_filter
	 */
	private function suppress_hooks( $suppressed_plugins ) {
		global $wp_filter;
		foreach ( $wp_filter as $tag => $filter ) {
			foreach ( $filter->callbacks as $priority => $prioritized_callbacks ) {
				foreach ( $prioritized_callbacks as $callback ) {
					if ( $this->is_callback_plugin_suppressed( $callback['function'], $suppressed_plugins ) ) {
						$filter->remove_filter( $tag, $callback['function'], $priority );
					}
				}
			}
		}
	}

	/**
	 * Suppress plugin shortcodes.
	 *
	 * @param string[] $suppressed_plugins Suppressed plugins.
	 * @global array $shortcode_tags
	 */
	private function suppress_shortcodes( $suppressed_plugins ) {
		global $shortcode_tags;

		foreach ( array_keys( $shortcode_tags ) as $tag ) {
			if ( $this->is_callback_plugin_suppressed( $shortcode_tags[ $tag ], $suppressed_plugins ) ) {
				add_shortcode( $tag, '__return_empty_string' );
			}
		}
	}

	/**
	 * Suppress plugin blocks.
	 *
	 * @todo What about static blocks added?
	 *
	 * @param string[] $suppressed_plugins Suppressed plugins.
	 */
	private function suppress_blocks( $suppressed_plugins ) {
		if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
			return;
		}

		$registry = WP_Block_Type_Registry::get_instance();

		foreach ( $registry->get_all_registered() as $block_type ) {
			if ( ! $block_type->is_dynamic() ) {
				continue;
			}

			if (
				$this->is_callback_plugin_suppressed( $block_type->render_callback, $suppressed_plugins )
				||
				(
					isset( $this->original_block_render_callbacks[ $block_type->name ] )
					&&
					$this->is_callback_plugin_suppressed( $this->original_block_render_callbacks[ $block_type->name ], $suppressed_plugins )
				)
			) {
				unset( $block_type->script, $block_type->style );
				$block_type->render_callback = '__return_empty_string';
			}
		}
	}

	/**
	 * Suppress plugin widgets.
	 *
	 * @see AMP_Validation_Manager::wrap_widget_callbacks() Which needs to run after this.
	 *
	 * @param string[] $suppressed_plugins Suppressed plugins.
	 * @global array $wp_registered_widgets
	 */
	private function suppress_widgets( $suppressed_plugins ) {
		global $wp_registered_widgets;
		foreach ( $wp_registered_widgets as &$registered_widget ) {
			if ( $this->is_callback_plugin_suppressed( $registered_widget['callback'], $suppressed_plugins ) ) {
				// This is primarily needed for widgets registered without WP_Widget.
				$registered_widget['callback'] = '__return_null';
			}
		}

		// The above will ensure that widgets registered via WP_Widget or wp_register_sidebar_widget() will both be
		// suppressed from being output. One additional case, which also applies to WP_Widget, is when the_widget()
		// is used to render a widget. For that, the 'widget_display_callback' filter below is used (in WP>=5.3).

		add_filter(
			'widget_display_callback',
			/**
			 * Prevent WP_Widgets from suppressed plugins from being rendered in sidebars and via the_widget().
			 *
			 * @param array     $instance   The current widget instance's settings.
			 * @param WP_Widget $widget_obj The current widget instance.
			 * @return array|false Instance or false if suppressed.
			 */
			function ( $instance, $widget_obj ) use ( $suppressed_plugins ) {
				if ( $this->is_callback_plugin_suppressed( [ $widget_obj, 'display_callback' ], $suppressed_plugins ) ) {
					$instance = false;
				}
				return $instance;
			},
			PHP_INT_MAX,
			2
		);
	}

	/**
	 * Determine whether callback is from a suppressed plugin.
	 *
	 * @param callable $callback           Callback.
	 * @param string[] $suppressed_plugins Suppressed plugins.
	 * @return bool Whether from suppressed plugin.
	 */
	private function is_callback_plugin_suppressed( $callback, $suppressed_plugins ) {
		$source = $this->callback_reflection->get_source( $callback );
		return (
			isset( $source['type'], $source['name'] ) &&
			'plugin' === $source['type'] &&
			in_array( $source['name'], $suppressed_plugins, true )
		);
	}
}
