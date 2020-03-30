<?php
/**
 * AMP meta box settings.
 *
 * @package AMP
 * @since 0.6
 */

/**
 * Post meta box class.
 *
 * @since 0.6
 */
class AMP_Post_Meta_Box {

	/**
	 * Assets handle.
	 *
	 * @since 0.6
	 * @var string
	 */
	const ASSETS_HANDLE = 'amp-post-meta-box';

	/**
	 * Block asset handle.
	 *
	 * @since 1.0
	 * @var string
	 */
	const BLOCK_ASSET_HANDLE = 'amp-block-editor';

	/**
	 * The enabled status post meta value.
	 *
	 * @since 0.6
	 * @var string
	 */
	const ENABLED_STATUS = 'enabled';

	/**
	 * The disabled status post meta value.
	 *
	 * @since 0.6
	 * @var string
	 */
	const DISABLED_STATUS = 'disabled';

	/**
	 * The status post meta key.
	 *
	 * @since 0.6
	 * @var string
	 */
	const STATUS_POST_META_KEY = 'amp_status';

	/**
	 * The field name for the enabled/disabled radio buttons.
	 *
	 * @since 0.6
	 * @var string
	 */
	const STATUS_INPUT_NAME = 'amp_status';

	/**
	 * The nonce name.
	 *
	 * @since 0.6
	 * @var string
	 */
	const NONCE_NAME = 'amp-status-nonce';

	/**
	 * The nonce action.
	 *
	 * @since 0.6
	 * @var string
	 */
	const NONCE_ACTION = 'amp-update-status';

	/**
	 * Initialize.
	 *
	 * @since 0.6
	 */
	public function init() {
		register_meta(
			'post',
			self::STATUS_POST_META_KEY,
			[
				'sanitize_callback' => [ $this, 'sanitize_status' ],
				'type'              => 'string',
				'description'       => __( 'AMP status.', 'amp' ),
				'show_in_rest'      => true,
				'single'            => true,
			]
		);

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_assets' ] );
		add_action( 'post_submitbox_misc_actions', [ $this, 'render_status' ] );
		add_action( 'save_post', [ $this, 'save_amp_status' ] );
		add_filter( 'preview_post_link', [ $this, 'preview_post_link' ] );
	}

	/**
	 * Sanitize status.
	 *
	 * @param string $status Status.
	 * @return string Sanitized status. Empty string when invalid.
	 */
	public function sanitize_status( $status ) {
		$status = strtolower( trim( $status ) );
		if ( ! in_array( $status, [ self::ENABLED_STATUS, self::DISABLED_STATUS ], true ) ) {
			/*
			 * In lieu of actual validation being available, clear the status entirely
			 * so that the underlying default status will be used instead.
			 * In the future it would be ideal if register_meta() accepted a
			 * validate_callback as well which the REST API could leverage.
			 */
			$status = '';
		}
		return $status;
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @since 0.6
	 */
	public function enqueue_admin_assets() {
		$post     = get_post();
		$screen   = get_current_screen();
		$validate = (
			isset( $screen->base ) &&
			'post' === $screen->base &&
			( ! isset( $screen->is_block_editor ) || ! $screen->is_block_editor ) &&
			is_post_type_viewable( $post->post_type )
		);

		if ( ! $validate ) {
			return;
		}

		wp_enqueue_style(
			self::ASSETS_HANDLE,
			amp_get_asset_url( 'css/amp-post-meta-box.css' ),
			false,
			AMP__VERSION
		);

		wp_styles()->add_data( self::ASSETS_HANDLE, 'rtl', 'replace' );

		$asset_file   = AMP__DIR__ . '/assets/js/' . self::ASSETS_HANDLE . '.asset.php';
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
		wp_enqueue_script(
			self::ASSETS_HANDLE,
			amp_get_asset_url( 'js/' . self::ASSETS_HANDLE . '.js' ),
			$dependencies,
			$version,
			false
		);

		if ( current_theme_supports( AMP_Theme_Support::SLUG ) ) {
			$availability   = AMP_Theme_Support::get_template_availability( $post );
			$support_errors = $availability['errors'];
		} else {
			$support_errors = AMP_Post_Type_Support::get_support_errors( $post );
		}

		wp_add_inline_script(
			self::ASSETS_HANDLE,
			sprintf(
				'ampPostMetaBox.boot( %s );',
				wp_json_encode(
					[
						'previewLink'     => esc_url_raw( add_query_arg( amp_get_slug(), '', get_preview_post_link( $post ) ) ),
						'canonical'       => amp_is_canonical(),
						'enabled'         => empty( $support_errors ),
						'canSupport'      => 0 === count( array_diff( $support_errors, [ 'post-status-disabled' ] ) ),
						'statusInputName' => self::STATUS_INPUT_NAME,
						'l10n'            => [
							'ampPreviewBtnLabel' => __( 'Preview changes in AMP (opens in new window)', 'amp' ),
						],
					]
				)
			)
		);
	}

	/**
	 * Enqueues block assets.
	 *
	 * @since 1.0
	 */
	public function enqueue_block_assets() {
		$post = get_post();
		if ( ! is_post_type_viewable( $post->post_type ) ) {
			return;
		}

		wp_enqueue_style(
			self::BLOCK_ASSET_HANDLE,
			amp_get_asset_url( 'css/' . self::BLOCK_ASSET_HANDLE . '.css' ),
			[],
			AMP__VERSION
		);

		wp_styles()->add_data( self::BLOCK_ASSET_HANDLE, 'rtl', 'replace' );

		$asset_file   = AMP__DIR__ . '/assets/js/' . self::BLOCK_ASSET_HANDLE . '.asset.php';
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		wp_enqueue_script(
			self::BLOCK_ASSET_HANDLE,
			amp_get_asset_url( 'js/' . self::BLOCK_ASSET_HANDLE . '.js' ),
			$dependencies,
			$version,
			true
		);

		$status_and_errors = self::get_status_and_errors( get_post() );
		$enabled_status    = $status_and_errors['status'];
		$error_messages    = $this->get_error_messages( $status_and_errors['status'], $status_and_errors['errors'] );

		$data = [
			'ampSlug'          => amp_get_slug(),
			'possibleStatuses' => [ self::ENABLED_STATUS, self::DISABLED_STATUS ],
			'defaultStatus'    => $enabled_status,
			'errorMessages'    => $error_messages,
			'hasThemeSupport'  => current_theme_supports( AMP_Theme_Support::SLUG ),
			'isStandardMode'   => amp_is_canonical(),
		];

		wp_localize_script(
			self::BLOCK_ASSET_HANDLE,
			'ampBlockEditor',
			$data
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( self::BLOCK_ASSET_HANDLE, 'amp' );
		} elseif ( function_exists( 'wp_get_jed_locale_data' ) || function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$locale_data  = function_exists( 'wp_get_jed_locale_data' ) ? wp_get_jed_locale_data( 'amp' ) : gutenberg_get_jed_locale_data( 'amp' );
			$translations = wp_json_encode( $locale_data );

			wp_add_inline_script(
				self::BLOCK_ASSET_HANDLE,
				'wp.i18n.setLocaleData( ' . $translations . ', "amp" );',
				'after'
			);
		}
	}

	/**
	 * Render AMP status.
	 *
	 * @since 0.6
	 * @param WP_Post $post Post.
	 */
	public function render_status( $post ) {
		$verify = (
			isset( $post->ID )
			&&
			is_post_type_viewable( $post->post_type )
			&&
			current_user_can( 'edit_post', $post->ID )
		);

		if ( true !== $verify ) {
			return;
		}

		$status_and_errors = self::get_status_and_errors( $post );
		$status            = $status_and_errors['status'];
		$errors            = $status_and_errors['errors'];

		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis
		$error_messages = $this->get_error_messages( $status, $errors );

		$labels = [
			'enabled'  => __( 'Enabled', 'amp' ),
			'disabled' => __( 'Disabled', 'amp' ),
		];
		// phpcs:enable VariableAnalysis.CodeAnalysis.VariableAnalysis

		// The preceding variables are used inside the following amp-status.php template.
		include AMP__DIR__ . '/includes/templates/amp-enabled-classic-editor-toggle.php';
	}

	/**
	 * Gets the AMP enabled status and errors.
	 *
	 * @since 1.0
	 * @param WP_Post $post The post to check.
	 * @return array {
	 *     The status and errors.
	 *
	 *     @type string    $status The AMP enabled status.
	 *     @type string[]  $errors AMP errors.
	 * }
	 */
	public static function get_status_and_errors( $post ) {
		/*
		 * When theme support is present then theme templates can be served in AMP and we check first if the template is available.
		 * Checking for template availability will include a check for get_support_errors. Otherwise, if theme support is not present
		 * then we just check get_support_errors.
		 */
		if ( current_theme_supports( AMP_Theme_Support::SLUG ) ) {
			$availability = AMP_Theme_Support::get_template_availability( $post );
			$status       = $availability['supported'] ? self::ENABLED_STATUS : self::DISABLED_STATUS;
			$errors       = array_diff( $availability['errors'], [ 'post-status-disabled' ] ); // Subtract the status which the metabox will allow to be toggled.
			if ( true === $availability['immutable'] ) {
				$errors[] = 'status_immutable';
			}
		} else {
			$errors = AMP_Post_Type_Support::get_support_errors( $post );
			$status = empty( $errors ) ? self::ENABLED_STATUS : self::DISABLED_STATUS;
			$errors = array_diff( $errors, [ 'post-status-disabled' ] ); // Subtract the status which the metabox will allow to be toggled.
		}

		return compact( 'status', 'errors' );
	}

	/**
	 * Gets the AMP enabled error message(s).
	 *
	 * @since 1.0
	 * @param string $status The AMP enabled status.
	 * @param array  $errors The AMP enabled errors.
	 * @return array $error_messages The error messages, as an array of strings.
	 */
	public function get_error_messages( $status, $errors ) {
		$error_messages = [];
		if ( in_array( 'status_immutable', $errors, true ) ) {
			if ( self::ENABLED_STATUS === $status ) {
				$error_messages[] = __( 'Your site does not allow AMP to be disabled.', 'amp' );
			} else {
				$error_messages[] = __( 'Your site does not allow AMP to be enabled.', 'amp' );
			}
		}
		if ( in_array( 'template_unsupported', $errors, true ) || in_array( 'no_matching_template', $errors, true ) ) {
			$error_messages[] = sprintf(
				/* translators: %s is a link to the AMP settings screen */
				__( 'There are no <a href="%s">supported templates</a> to display this in AMP.', 'amp' ),
				esc_url( admin_url( 'admin.php?page=' . AMP_Options_Manager::OPTION_NAME ) )
			);
		}
		if ( in_array( 'password-protected', $errors, true ) ) {
			$error_messages[] = __( 'AMP cannot be enabled on password protected posts.', 'amp' );
		}
		if ( in_array( 'post-type-support', $errors, true ) ) {
			$error_messages[] = sprintf(
				/* translators: %s is a link to the AMP settings screen */
				__( 'AMP cannot be enabled because this <a href="%s">post type does not support it</a>.', 'amp' ),
				esc_url( admin_url( 'admin.php?page=' . AMP_Options_Manager::OPTION_NAME ) )
			);
		}
		if ( in_array( 'skip-post', $errors, true ) ) {
			$error_messages[] = __( 'A plugin or theme has disabled AMP support.', 'amp' );
		}
		if ( count( array_diff( $errors, [ 'status_immutable', 'page-on-front', 'page-for-posts', 'password-protected', 'post-type-support', 'skip-post', 'template_unsupported', 'no_matching_template' ] ) ) > 0 ) {
			$error_messages[] = __( 'Unavailable for an unknown reason.', 'amp' );
		}

		return $error_messages;
	}

	/**
	 * Save AMP Status.
	 *
	 * @since 0.6
	 * @param int $post_id The Post ID.
	 */
	public function save_amp_status( $post_id ) {
		$verify = (
			isset( $_POST[ self::NONCE_NAME ], $_POST[ self::STATUS_INPUT_NAME ] )
			&&
			wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION )
			&&
			current_user_can( 'edit_post', $post_id )
			&&
			! wp_is_post_revision( $post_id )
			&&
			! wp_is_post_autosave( $post_id )
		);

		if ( true === $verify ) {
			update_post_meta(
				$post_id,
				self::STATUS_POST_META_KEY,
				$_POST[ self::STATUS_INPUT_NAME ] // Note: The sanitize_callback has been supplied in the register_meta() call above.
			);
		}
	}

	/**
	 * Modify post preview link.
	 *
	 * Add the AMP query var is the amp-preview flag is set.
	 *
	 * @since 0.6
	 *
	 * @param string $link The post preview link.
	 * @return string Preview URL.
	 */
	public function preview_post_link( $link ) {
		$is_amp = (
			isset( $_POST['amp-preview'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			&&
			'do-preview' === sanitize_key( wp_unslash( $_POST['amp-preview'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
		);

		if ( $is_amp ) {
			$link = add_query_arg( amp_get_slug(), true, $link );
		}

		return $link;
	}
}
