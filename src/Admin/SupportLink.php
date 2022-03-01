<?php
/**
 * Service Link class that adds support links throughout the plugin's UI.
 *
 * @package Ampproject\Ampwp
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Validated_URL_Post_Type;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Admin_Bar;
use WP_Post;

/**
 * Service that adds support links throughout the plugin's UI.
 *
 * @since 2.2
 * @internal
 */
class SupportLink implements Service, Delayed, Conditional, Registerable {

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'wp_loaded';
	}

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return (
			SupportScreen::check_core_version()
			&&
			SupportScreen::has_cap()
		);
	}

	/**
	 * Adds hooks.
	 *
	 * @return void
	 */
	public function register() {

		// Add support link to Admin Bar.
		add_action( 'admin_bar_menu', [ $this, 'admin_bar_menu' ], 105 );

		if ( is_admin() ) {
			// Add support link to meta box.
			add_filter( 'amp_validated_url_status_actions', [ $this, 'amp_validated_url_status_actions' ], 10, 2 );

			// Add support link to Post row actions.
			add_filter( 'post_row_actions', [ $this, 'post_row_actions' ], PHP_INT_MAX, 2 );

			// Plugin row Support link.
			add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
		}
	}

	/**
	 * Add Diagnostic link to Admin Bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar object.
	 *
	 * @return void
	 */
	public function admin_bar_menu( WP_Admin_Bar $wp_admin_bar ) {

		if ( ! $wp_admin_bar->get_node( 'amp' ) ) {
			return;
		}

		$wp_admin_bar->add_node(
			[
				'parent' => 'amp',
				'title'  => esc_html__( 'Get support', 'amp' ),
				'id'     => 'amp-support',
				'href'   => esc_url(
					add_query_arg(
						[
							'page' => 'amp-support',
							'url'  => rawurlencode( amp_get_current_url() ),
						],
						admin_url( 'admin.php' )
					)
				),
			]
		);
	}

	/**
	 * Add support link to meta box.
	 *
	 * @param string[] $actions Array of actions.
	 * @param WP_Post  $post    Referenced WP_Post object.
	 *
	 * @return string[] $actions Array of actions.
	 */
	public function amp_validated_url_status_actions( $actions, WP_Post $post ) {

		if ( AMP_Validated_URL_Post_Type::POST_TYPE_SLUG !== $post->post_type ) {
			return $actions;
		}

		$query_args = [
			'page' => 'amp-support',
			'url'  => rawurlencode( AMP_Validated_URL_Post_Type::get_url_from_post( $post ) ),
		];

		$actions['amp-support'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( add_query_arg( $query_args, admin_url( 'admin.php' ) ) ),
			esc_html__( 'Get Support', 'amp' )
		);

		return $actions;
	}

	/**
	 * Add support link to Post row actions.
	 *
	 * @param string[] $actions Array of actions.
	 * @param WP_Post  $post    Referenced WP_Post object.
	 *
	 * @return string[] Array of actions
	 */
	public function post_row_actions( $actions, WP_Post $post ) {

		if ( AMP_Validated_URL_Post_Type::POST_TYPE_SLUG !== $post->post_type ) {
			return $actions;
		}

		$query_args = [
			'page' => 'amp-support',
			'url'  => rawurlencode( AMP_Validated_URL_Post_Type::get_url_from_post( $post ) ),
		];

		$actions['amp-support'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( add_query_arg( $query_args, admin_url( 'admin.php' ) ) ),
			esc_html__( 'Get Support', 'amp' )
		);

		return $actions;
	}

	/**
	 * Plugin row Support link.
	 *
	 * @param string[] $plugin_meta An array of the plugin's metadata, including the version, author, author URI, and
	 *                              plugin URI.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 *
	 * @return string[] Filtered array of plugin's metadata.
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {

		if ( 'amp/amp.php' === $plugin_file ) {
			$plugin_meta[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						[ 'page' => 'amp-support' ],
						admin_url( 'admin.php' )
					)
				),
				esc_html__( 'Get support', 'amp' )
			);
		}

		return $plugin_meta;
	}
}
