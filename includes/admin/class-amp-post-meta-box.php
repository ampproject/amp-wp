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
	 * @var string
	 */
	const ASSETS_HANDLE = 'amp-post-meta-box';

	/**
	 * Initialize.
	 *
	 * @since 0.6
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_filter( 'preview_post_link', array( $this, 'preview_post_link' ) );
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @since 0.6
	 * @param string $hook_suffix The current admin page.
	 * @return Void Void on failure.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		$post     = get_post();
		$validate = (
			true === (bool) preg_match( '#(post|post-new).php#', $hook_suffix )
			&&
			true === post_supports_amp( $post )
		);

		if ( true !== $validate ) {
			return;
		}

		// Styles.
		wp_enqueue_style(
			self::ASSETS_HANDLE,
			amp_get_asset_url( 'css/amp-post-meta-box.css' ),
			false,
			AMP__VERSION
		);

		// Scripts.
		wp_enqueue_script(
			self::ASSETS_HANDLE,
			amp_get_asset_url( 'js/amp-post-meta-box.js' ),
			array( 'jquery' ),
			AMP__VERSION
		);
		wp_add_inline_script( self::ASSETS_HANDLE, sprintf( 'ampPostMetaBox.boot( %s );',
			wp_json_encode( array(
				'previewLink' => esc_url_raw( add_query_arg( AMP_QUERY_VAR, true, get_preview_post_link( $post ) ) ),
			) )
		) );
	}

	/**
	 * Modify post preview link.
	 *
	 * Add the AMP query var is the amp-preview flag is set.
	 *
	 * @param string $link The post preview link.
	 * @since 0.6
	 */
	public function preview_post_link( $link ) {
		$is_amp = (
			isset( $_POST['amp-preview'] ) // WPCS: CSRF ok.
			&&
			'do-preview' === sanitize_key( wp_unslash( $_POST['amp-preview'] ) ) // WPCS: CSRF ok.
		);

		if ( $is_amp ) {
			$link = add_query_arg( AMP_QUERY_VAR, true, $link );
		}

		return $link;
	}

}
