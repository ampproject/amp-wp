<?php
/**
 * AMP meta box settings.
 *
 * @package AMP
 * @since 0.6
 */

use AmpProject\AmpWP\Services;

/**
 * Post meta box class.
 *
 * @since 0.6
 * @internal
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
	 * The name for the REST API field containing whether AMP is enabled for a post.
	 *
	 * @since 2.0
	 * @var string
	 */
	const REST_ATTRIBUTE_NAME = 'amp_enabled';

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
				'auth_callback'     => '__return_false',
				'type'              => 'string',
				'description'       => __( 'AMP status.', 'amp' ),
				'show_in_rest'      => false,
				'single'            => true,
			]
		);

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_assets' ] );
		add_action( 'post_submitbox_misc_actions', [ $this, 'render_status' ] );
		add_action( 'save_post', [ $this, 'save_amp_status' ] );
		add_action( 'rest_api_init', [ $this, 'add_rest_api_fields' ] );
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
			empty( $screen->is_block_editor ) &&
			in_array( $post->post_type, AMP_Post_Type_Support::get_supported_post_types(), true )
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

		// Abort if version of WordPress is too old.
		if ( ! Services::get( 'dependency_support' )->has_support_from_core() ) {
			return;
		}

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

		if ( ! amp_is_legacy() ) {
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
						'previewLink'     => esc_url_raw( amp_add_paired_endpoint( get_preview_post_link( $post ) ) ),
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

		// Block validation script uses features only available beginning with WP 5.6.
		$dependency_support = Services::get( 'dependency_support' );
		if ( ! $dependency_support->has_support() ) {
			return; // @codeCoverageIgnore
		}

		// Only enqueue scripts on the block editor for AMP-enabled posts.
		$editor_support = Services::get( 'editor.editor_support' );
		if ( ! $editor_support->is_current_screen_block_editor_for_amp_enabled_post_type() ) {
			return;
		}

		$status_and_errors = self::get_status_and_errors( $post );

		// Skip proceeding if there are errors blocking AMP and the user can't do anything about it.
		if ( ! empty( $status_and_errors['errors'] ) && ! current_user_can( 'manage_options' ) ) {
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

		$is_standard_mode = amp_is_canonical();

		list( $featured_image_minimum_width, $featured_image_minimum_height ) = self::get_featured_image_dimensions();

		$data = [
			'ampUrl'                     => $is_standard_mode ? null : amp_add_paired_endpoint( get_permalink( $post ) ),
			'ampPreviewLink'             => $is_standard_mode ? null : amp_add_paired_endpoint( get_preview_post_link( $post ) ),
			'errorMessages'              => $this->get_error_messages( $status_and_errors['errors'] ),
			'hasThemeSupport'            => ! amp_is_legacy(),
			'isDevToolsEnabled'          => Services::get( 'dev_tools.user_access' )->is_user_enabled(),
			'isStandardMode'             => $is_standard_mode,
			'featuredImageMinimumWidth'  => $featured_image_minimum_width,
			'featuredImageMinimumHeight' => $featured_image_minimum_height,
			'ampBlocksInUse'             => $is_standard_mode ? $this->get_amp_blocks_in_use() : [],
		];

		wp_add_inline_script(
			self::BLOCK_ASSET_HANDLE,
			sprintf( 'var ampBlockEditor = %s;', wp_json_encode( $data ) ),
			'before'
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
	 * Returns a tuple of width and height featured image dimensions after filtering.
	 *
	 * @return int[] {
	 *     Minimum dimensions.
	 *
	 *     @type int $0 Image width in pixels. May be zero to disable the dimension constraint.
	 *     @type int $1 Image height in pixels. May be zero to disable the dimension constraint.
	 * }
	 */
	public static function get_featured_image_dimensions() {
		$default_width  = 1200;
		$default_height = 675;

		/**
		 * Filters the minimum height required for a featured image.
		 *
		 * @since 2.0.9
		 *
		 * @param int $featured_image_minimum_height The minimum height of the image, defaults to 675.
		 *                                           Returning a number less than or equal to zero disables the minimum constraint.
		 */
		$featured_image_minimum_height = (int) apply_filters( 'amp_featured_image_minimum_height', $default_height );

		/**
		 * Filters the minimum width required for a featured image.
		 *
		 * @since 2.0.9
		 *
		 * @param int $featured_image_minimum_width The minimum width of the image, defaults to 1200.
		 *                                          Returning a number less than or equal to zero disables the minimum constraint.
		 */
		$featured_image_minimum_width = (int) apply_filters( 'amp_featured_image_minimum_width', $default_width );

		return [
			max( $featured_image_minimum_width, 0 ),
			max( $featured_image_minimum_height, 0 ),
		];
	}

	/**
	 * Render AMP status.
	 *
	 * @since 0.6
	 * @param WP_Post $post Post.
	 */
	public function render_status( $post ) {
		$verify = (
			! empty( $post->ID )
			&&
			in_array( $post->post_type, AMP_Post_Type_Support::get_supported_post_types(), true )
			&&
			current_user_can( 'edit_post', $post->ID )
		);

		if ( true !== $verify ) {
			return;
		}

		$status_and_errors = self::get_status_and_errors( $post );
		$status            = $status_and_errors['status']; // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable -- Used in amp-enabled-classic-editor-toggle.php.
		$errors            = $status_and_errors['errors'];

		// Skip showing any error message if the user doesn't have the ability to do anything about it.
		if ( ! empty( $errors ) && ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis
		$error_messages = $this->get_error_messages( $errors );

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
		if ( ! amp_is_legacy() ) {
			$availability = AMP_Theme_Support::get_template_availability( $post );
			$status       = $availability['supported'] ? self::ENABLED_STATUS : self::DISABLED_STATUS;
			$errors       = array_diff( $availability['errors'], [ 'post-status-disabled' ] ); // Subtract the status which the metabox will allow to be toggled.
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
	 * @see AMP_Post_Type_Support::get_support_errors()
	 *
	 * @param string[] $errors The AMP enabled errors.
	 * @return array $error_messages The error messages, as an array of strings.
	 */
	public function get_error_messages( $errors ) {
		$settings_screen_url = admin_url( 'admin.php?page=' . AMP_Options_Manager::OPTION_NAME );

		$error_messages = [];
		if ( in_array( 'template_unsupported', $errors, true ) || in_array( 'no_matching_template', $errors, true ) ) {
			$error_messages[] = sprintf(
				/* translators: %s is a link to the AMP settings screen */
				__( 'There are no <a href="%s" target="_blank">supported templates</a>.', 'amp' ),
				esc_url( $settings_screen_url )
			);
		}
		if ( in_array( 'post-type-support', $errors, true ) ) {
			$error_messages[] = sprintf(
				/* translators: %s is a link to the AMP settings screen */
				__( 'This post type is not <a href="%s" target="_blank">enabled</a>.', 'amp' ),
				esc_url( $settings_screen_url )
			);
		}
		if ( in_array( 'skip-post', $errors, true ) ) {
			$error_messages[] = __( 'A plugin or theme has disabled AMP support.', 'amp' );
		}
		if ( in_array( 'invalid-post', $errors, true ) ) {
			$error_messages[] = __( 'The post data could not be successfully retrieved.', 'amp' );
		}
		if ( count( array_diff( $errors, [ 'post-type-support', 'skip-post', 'template_unsupported', 'no_matching_template', 'invalid-post' ] ) ) > 0 ) {
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
				$_POST[ self::STATUS_INPUT_NAME ] // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The sanitize_callback has been supplied in the register_meta() call above.
			);
		}
	}

	/**
	 * Modify post preview link.
	 *
	 * Add the AMP query var if the amp-preview flag is set.
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
			$link = amp_add_paired_endpoint( $link );
		}

		return $link;
	}

	/**
	 * Add a REST API field to display whether AMP is enabled on supported post types.
	 *
	 * @since 2.0
	 *
	 * @return void
	 */
	public function add_rest_api_fields() {
		register_rest_field(
			AMP_Post_Type_Support::get_post_types_for_rest_api(),
			self::REST_ATTRIBUTE_NAME,
			[
				'get_callback'    => [ $this, 'get_amp_enabled_rest_field' ],
				'update_callback' => [ $this, 'update_amp_enabled_rest_field' ],
				'schema'          => [
					'description' => __( 'AMP enabled', 'amp' ),
					'type'        => 'boolean',
				],
			]
		);
	}

	/**
	 * Get the value of whether AMP is enabled for a REST API request.
	 *
	 * @since 2.0
	 *
	 * @param array $post_data Post data.
	 * @return bool Whether AMP is enabled on post.
	 */
	public function get_amp_enabled_rest_field( $post_data ) {
		$status = $this->sanitize_status( get_post_meta( $post_data['id'], self::STATUS_POST_META_KEY, true ) );

		if ( '' === $status ) {
			$post              = get_post( $post_data['id'] );
			$status_and_errors = self::get_status_and_errors( $post );

			if ( isset( $status_and_errors['status'] ) ) {
				$status = $status_and_errors['status'];
			}
		}

		return self::ENABLED_STATUS === $status;
	}

	/**
	 * Update whether AMP is enabled for a REST API request.
	 *
	 * @since 2.0
	 *
	 * @param bool    $is_enabled Whether AMP is enabled.
	 * @param WP_Post $post       Post being updated.
	 * @return null|WP_Error Null on success, WP_Error object on failure.
	 */
	public function update_amp_enabled_rest_field( $is_enabled, $post ) {
		if ( ! in_array( $post->post_type, AMP_Post_Type_Support::get_post_types_for_rest_api(), true ) ) {
			return new WP_Error(
				'rest_invalid_post_type',
				sprintf(
					/* translators: %s: The name of the post type. */
					__( 'AMP is not supported for the "%s" post type.', 'amp' ),
					$post->post_type
				),
				[ 'status' => 400 ]
			);
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return new WP_Error(
				'rest_insufficient_permission',
				__( 'Insufficient permissions to change whether AMP is enabled.', 'amp' ),
				[ 'status' => 403 ]
			);
		}

		$status = $is_enabled ? self::ENABLED_STATUS : self::DISABLED_STATUS;

		// Note: The sanitize_callback has been supplied in the register_meta() call above.
		$updated = update_post_meta(
			$post->ID,
			self::STATUS_POST_META_KEY,
			$status
		);

		if ( false === $updated ) {
			return new WP_Error(
				'rest_update_failed',
				__( 'The AMP enabled status failed to be updated.', 'amp' ),
				[ 'status' => 500 ]
			);
		}

		return null;
	}

	/**
	 * Get the list of AMP block names used in the current post.
	 *
	 * @since 2.1
	 *
	 * @return string[]
	 */
	public function get_amp_blocks_in_use() {
		// Normalize the AMP block names to include the `amp/` namespace.
		$amp_blocks        = substr_replace( AMP_Editor_Blocks::AMP_BLOCKS, 'amp/', 0, 0 );
		$amp_blocks_in_use = array_filter( $amp_blocks, 'has_block' );

		return array_values( $amp_blocks_in_use );
	}
}
