<?php
/**
 * Reference site export options.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Export;

use AmpProject\AmpWP\Tests\Cli\ExportStep;

final class ExportOptions implements ExportStep {

	/**
	 * Array of regular expressions of option keys to skip.
	 *
	 * @var string[]
	 */
	const EXCLUDED_OPTIONS = [
		'/^(_site)?_transient_.*$/',
		'/^admin_email_lifespan$/',
		'/^amp-options$/',
		'/^amp_css_transient_monitor_time_series$/',
		'/^_bp_.*$/',
		'/^bp-(blogs-first-install|disable-account-deletion|disable-cover-image-uploads|disable-group-avatar-uploads|disable-group-cover-image-uploads|disable-profile-sync|emails-unsubscribe-salt)$/',
		'/^_elementor_installed_time$/',
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
		'/^finished_splitting_shared_terms$/',
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
		'/^page_on_front$/',
		'/^permalink_structure$/',
		'/^ping_sites$/',
		'/^post_count$/',
		'/^recently_(activated|edited)$/',
		'/^recovery_(keys|mode_email_last_sent)$/',
		'/^require_name_email$/',
		'/^rewrite_rules$/',
		'/^sharing-options$/',
		'/^show_avatars$/',
		'/^show_comments_cookies_opt_in$/',
		'/^show_on_front$/',
		'/^sidebars_widgets$/',
		'/^site_icon$/',
		'/^siteurl$/',
		'/^start_of_week$/',
		'/^stats_options$/',
		'/^sticky_posts$/',
		'/^supercache_stats$/',
		'/^tag_base$/',
		'/^the_seo_framework_(initial_db_version|tested_upgrade_version|upgraded_db_version)$/',
		'/^theme_switched$/',
		'/^timezone_string$/',
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
		'/^wp_user_roles$/',
		'/^wpseo_(flush_rewrite|ryte)$/',
		'/^wpsupercache_.*$/',
	];

	/**
	 * SQL query to fetch all option keys.
	 *
	 * This includes a placeholder for the table name.
	 *
	 * @var string
	 */
	const OPTION_KEYS_SQL_QUERY = 'SELECT `option_name` FROM `%s`;';

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
	 * @return array Array of options to export.
	 */
	private function fetch_options( $keys ) {
		global $wpdb;

		if ( empty( $keys ) ) {
			return [];
		}

		$query = sprintf(
			'SELECT `option_name`, `option_value` FROM `%s` WHERE `option_name` IN (%s);',
			$wpdb->options,
			implode(
				',',
				array_map( static function ( $key ) { return "'" . esc_sql( $key ) . "'"; }, $keys )
			)
		);

		$options = array_filter( (array) $wpdb->get_results( $query, 'ARRAY_A' ) );

		return array_combine(
			array_column( $options, 'option_name' ),
			array_column( $options, 'option_value' )
		);
	}

	/**
	 * Skip the options that are marked as excluded.
	 *
	 * @param string $option_key Option key to check.
	 * @return bool Whether to skip the option.
	 */
	private function skip_excluded_options( $option_key ) {
		foreach ( self::EXCLUDED_OPTIONS as $option_pattern ) {
			if ( 1 === preg_match( $option_pattern, $option_key ) ) {
				return false;
			}
		}

		return true;
	}
}
