<?php
/**
 * Reference site export options.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Export;

use AmpProject\AmpWP\Tests\Cli\ExportStep;
use WP_Post;

final class ExportOptions implements ExportStep {

	/**
	 * Array of regular expressions of option keys to skip.
	 *
	 * @var string[]
	 */
	const EXCLUDED_OPTIONS = [
		'/^__uagb_do_redirect$/',
		'/^(_site)?_transient_.*$/',
		'/^_bp_.*$/',
		'/^_elementor_installed_time$/',
		'/^action_scheduler_.*$/',
		'/^active_plugins$/',
		'/^admin_email(_lifespan)?$/',
		'/^amp-options$/',
		'/^amp_css_transient_monitor_time_series$/',
		'/^astra-settings$/',
		'/^bp-(blogs-first-install|disable-account-deletion|disable-cover-image-uploads|disable-group-avatar-uploads|disable-group-cover-image-uploads|disable-profile-sync|emails-unsubscribe-salt)$/',
		'/^auto_core_update_notified$/',
		'/^avatar_.*$/',
		'/^blacklist_.*$/',
		'/^blog(_charset|_public|description|name)$/',
		'/^bp_.*$/',
		'/^category_children$/',
		'/^close_comments_.*$/',
		'/^cookie_notice_version$/',
		'/^comment_.*$/',
		'/^comments_notify$/',
		'/^cron$/',
		'/^current_theme$/',
		'/^db_(upgraded|version)$/',
		'/^default_.*$/',
		'/^do_activate$/',
		'/^elementor_(controls_usage|log|remote_info_feed_data|remote_info_library|scheme_color-picker|version)$/',
		'/^finished_(updating_comment_type|splitting_shared_terms)$/',
		'/^fresh_site$/',
		'/^gmt_offset$/',
		'/^hack_file$/',
		'/^hide-loggedout-adminbar$/',
		'/^home$/',
		'/^html_type$/',
		'/^image_default_.*$/',
		'/^initial_db_version$/',
		'/^insecure_content$/',
		'/^is_https_supported$/',
		'/^jetpack_(callables|constants)_sync_checksum$/',
		'/^jetpack_(log|plugin_api_action_links|tos_agreed|ab_connect_banner_green_bar|activated|activation_source|available_modules)$/',
		'/^jetpack_(next_)?sync_.*$/',
		'/^jpsq_sync.*$/',
		'/^link_manager_enabled$/',
		'/^links_updated_date_format$/',
		'/^mailserver_.*$/',
		'/^moderation_(keys|notify)$/',
		'/^page_comments$/',
		'/^page_for_posts$/',
		'/^permalink_structure$/',
		'/^ping_sites$/',
		'/^post_count$/',
		'/^recently_(activated|edited)$/',
		'/^recovery_(keys|mode_email_last_sent)$/',
		'/^require_name_email$/',
		'/^rewrite_rules$/',
		'/^schema-ActionScheduler_.*$/',
		'/^sharing-options$/',
		'/^show_avatars$/',
		'/^show_comments_cookies_opt_in$/',
		'/^sidebars_widgets$/',
		'/^site_icon$/',
		'/^siteurl$/',
		'/^start_of_week$/',
		'/^stats_options$/',
		'/^sticky_posts$/',
		'/^supercache_stats$/',
		'/^tag_base$/',
		'/^the_seo_framework_(initial_db_version|tested_upgrade_version|upgraded_db_version)$/',
		'/^theme_mods_.*$/',
		'/^theme_switched$/',
		'/^timezone_string$/',
		'/^uagb-version$/',
		'/^uninstall_plugins$/',
		'/^upload_(path|space_check_disabled|url_path)$/',
		'/^uploads_use_yearmonth_folders$/',
		'/^users_can_register$/',
		'/^widget_.*$/',
		'/^woocommerce_(admin_notices|allow_bulk_remove_personal_data|allow_tracking|allowed_countries|anonymize_completed_orders|api_enabled|calc_discounts_sequentially|calc_taxes|cart_redirect_after_add|checkout_pay_endpoint|delete_inactive_accounts|demo_store|downloads_grant_access_after_payment|downloads_require_login|file_download_method|meta_box_errors|queue_flush_rewrite_rules|shipping_debug_mode|show_marketplace_suggestions)$/',
		'/^woocommerce_(db_)?version$/',
		'/^woocommerce_email_.*$/',
		'/^woocommerce_erasure_request_.*$/',
		'/^woocommerce_myaccount_.*$/',
		'/^woocommerce_notify_.*$/',
		'/^woocommerce_ship_to_.*$/',
		'/^woocommerce_specific_.*$/',
		'/^woocommerce_stock_(email_recipient|format)$/',
		'/^woocommerce_trash_.*$/',
		'/^wp_astra_theme_db_migration_.*$/',
		'/^wp_user_roles$/',
		'/^wpforms_(activated|version|version_lite)$/',
		'/^wpseo_(flush_rewrite|ryte)$/',
		'/^wpsupercache_.*$/',
	];

	/**
	 * Associative array of options and their default values for skipping.
	 *
	 * @var array
	 */
	const OPTION_DEFAULTS = [
		'use_balanceTags'                 => '0',
		'use_smilies'                     => '1',
		'posts_per_rss'                   => '10',
		'rss_use_excerpt'                 => '0',
		'posts_per_page'                  => '10',
		'date_format'                     => 'F j, Y',
		'time_format'                     => 'g:i a',
		'category_base'                   => '',
		'template'                        => 'astra',
		'stylesheet'                      => 'astra',
		'use_trackback'                   => '0',
		'thumbnail_size_w'                => '150',
		'thumbnail_size_h'                => '150',
		'thumbnail_crop'                  => '1',
		'medium_size_w'                   => '300',
		'medium_size_h'                   => '300',
		'large_size_w'                    => '1024',
		'large_size_h'                    => '1024',
		'thread_comments'                 => '1',
		'thread_comments_depth'           => '5',
		'comments_per_page'               => '50',
		'medium_large_size_w'             => '768',
		'medium_large_size_h'             => '0',
		'wp_page_for_privacy_policy'      => '0',
		'disallowed_keys'                 => '',
		'auto_plugin_theme_update_emails' => [],
		'__uagb_do_redirect'              => '',
	];

	/**
	 * Process the export step.
	 *
	 * @param ExportResult $export_result Export result to adapt.
	 *
	 * @return ExportResult Adapted export result.
	 */
	public function process( ExportResult $export_result ) {
		$option_keys = array_filter(
			$this->get_option_keys(),
			[ $this, 'skip_excluded_options' ]
		);

		$options = $this->fetch_options( $option_keys );
		$options = array_filter(
			$options,
			[ $this, 'skip_default_values' ],
			ARRAY_FILTER_USE_BOTH
		);

		$options = $this->adapt_options( $options );

		$export_result->add_step( 'import_options', compact( 'options' ) );

		return $export_result;
	}

	/**
	 * Get all options stored in the current WordPress instance.
	 *
	 * @return string[]
	 */
	private function get_option_keys() {
		global $wpdb;

		return $wpdb->get_col(
			sprintf( 'SELECT `option_name` FROM `%s`;', $wpdb->options )
		);
	}

	/**
	 * Fetch the options that are meant to be exported.
	 *
	 * @param string[] $keys Keys of the options to fetch.
	 * @return array Associative array of options to export.
	 */
	private function fetch_options( $keys ) {
		global $wpdb;

		if ( empty( $keys ) ) {
			return [];
		}

		$query = sprintf(
			'SELECT `option_name`, `option_value` FROM `%s` WHERE `option_name` IN (%s);',
			esc_sql( $wpdb->options ),
			implode(
				',',
				array_map(
					static function ( $key ) {
						return "'" . esc_sql( $key ) . "'"; },
					$keys
				)
			)
		);

		$options = array_filter( (array) $wpdb->get_results( $query, 'ARRAY_A' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Manually escaped a few lines higher.

		return array_combine(
			array_column( $options, 'option_name' ),
			array_map( 'maybe_unserialize', array_column( $options, 'option_value' ) )
		);
	}

	/**
	 * Adapt the options to get rid of hard-coded elements like IDs.
	 *
	 * @param array        $options       Associative array of options to adapt.
	 * @return array Adapted associative array of options.
	 */
	private function adapt_options( $options ) {
		foreach ( $options as $key => $value ) {
			switch ( $key ) {
				case 'woocommerce_shop_page_title':
				case 'woocommerce_cart_page_title':
				case 'woocommerce_checkout_page_title':
				case 'woocommerce_myaccount_page_title':
				case 'woocommerce_edit_address_page_title':
				case 'woocommerce_view_order_page_title':
				case 'woocommerce_change_password_page_title':
				case 'woocommerce_logout_page_title':
					// @TODO
					break;

				case 'page_for_posts':
				case 'page_on_front':
					$options[ $key ] = $this->get_page_title_from_post_id( (int) $value );
					break;

				case 'woocommerce_product_cat':
					// @TODO
					break;
			}
		}

		return $options;
	}

	/**
	 * Skip the options that are marked as excluded.
	 *
	 * @param string $option_key Option key to check.
	 * @return bool Whether to keep the option.
	 */
	private function skip_excluded_options( $option_key ) {
		foreach ( self::EXCLUDED_OPTIONS as $option_pattern ) {
			if ( 1 === preg_match( $option_pattern, $option_key ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Skip the options that still have their default value.
	 *
	 * @param string $option_value Option value to check.
	 * @param string $option_key   Option key to check.
	 * @return bool Whether to keep the option.
	 */
	private function skip_default_values( $option_value, $option_key ) {
		if ( ! array_key_exists( $option_key, self::OPTION_DEFAULTS ) ) {
			return true;
		}

		return self::OPTION_DEFAULTS[ $option_key ] !== $option_value;
	}

	/**
	 * Get the post title from a post ID.
	 *
	 * @param int $post_id ID of the post to get the title from.
	 * @return string Post title.
	 */
	private function get_page_title_from_post_id( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post || empty( $post->post_title ) ) {
			return $post_id;
		}

		return $post->post_title;
	}
}
