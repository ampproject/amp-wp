<?php
/**
 * Admin pointer class.
 *
 * @package AMP
 * @since 1.0
 */

/**
 * AMP_Admin_Pointer class.
 *
 * Outputs an admin pointer to show the new features of v1.0.
 * Based on https://code.tutsplus.com/articles/integrating-with-wordpress-ui-admin-pointers--wp-26853
 *
 * @since 1.0
 */
class AMP_Admin_Pointer {

	/**
	 * The ID of the template mode admin pointer.
	 *
	 * @var string
	 */
	const TEMPLATE_POINTER_ID = 'amp_template_mode_pointer_10';

	/**
	 * The slug of the script.
	 *
	 * @var string
	 */
	const SCRIPT_SLUG = 'amp-admin-pointer';

	/**
	 * The slug of the tooltip script.
	 *
	 * @var string
	 */
	const TOOLTIP_SLUG = 'amp-validation-tooltips';

	/**
	 * Initializes the class.
	 *
	 * @since 1.0
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_pointer' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_tooltips' ) );
	}

	/**
	 * Enqueues the pointer assets.
	 *
	 * If the pointer has not been dismissed, enqueues the style and script.
	 * And outputs the pointer data for the script.
	 *
	 * @since 1.0
	 */
	public function enqueue_pointer() {
		if ( $this->is_pointer_dismissed() ) {
			return;
		}

		wp_enqueue_style( 'wp-pointer' );

		wp_enqueue_script(
			self::SCRIPT_SLUG,
			amp_get_asset_url( 'js/' . self::SCRIPT_SLUG . '.js' ),
			array( 'jquery', 'wp-pointer' ),
			AMP__VERSION,
			true
		);

		wp_add_inline_script(
			self::SCRIPT_SLUG,
			sprintf( 'ampAdminPointer.load( %s );', wp_json_encode( $this->get_pointer_data() ) )
		);
	}

	/**
	 * Registers style and script for tooltips.
	 *
	 * @since 1.0
	 */
	public function register_tooltips() {
		wp_register_style(
			self::TOOLTIP_SLUG,
			amp_get_asset_url( 'css/' . self::TOOLTIP_SLUG . '.css' ),
			array( 'wp-pointer' ),
			AMP__VERSION
		);

		wp_register_script(
			self::TOOLTIP_SLUG,
			amp_get_asset_url( 'js/' . self::TOOLTIP_SLUG . '.js' ),
			array( 'jquery', 'wp-pointer' ),
			AMP__VERSION,
			true
		);
	}

	/**
	 * Whether the AMP admin pointer has been dismissed.
	 *
	 * @since 1.0
	 * @return boolean Is dismissed.
	 */
	protected function is_pointer_dismissed() {

		// Consider dismissed in v1.1, since admin pointer is only to educate about the new modes in 1.0.
		if ( version_compare( strtok( AMP__VERSION, '-' ), '1.1', '>=' ) ) {
			return true;
		}

		$dismissed = get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
		if ( empty( $dismissed ) ) {
			return false;
		}
		$dismissed = explode( ',', strval( $dismissed ) );

		return in_array( self::TEMPLATE_POINTER_ID, $dismissed, true );
	}

	/**
	 * Gets the pointer data to pass to the script.
	 *
	 * @since 1.0
	 * @return array Pointer data.
	 */
	public function get_pointer_data() {
		return array(
			'pointer' => array(
				'pointer_id' => self::TEMPLATE_POINTER_ID,
				'target'     => '#toplevel_page_amp-options',
				'options'    => array(
					'content'  => sprintf(
						'<h3>%s</h3><p><strong>%s</strong></p><p>%s</p>',
						__( 'AMP', 'amp' ),
						__( 'New AMP Template Modes', 'amp' ),
						__( 'You can now reuse your theme\'s templates and styles in AMP responses, in both &#8220;Transitional&#8221; and &#8220;Native&#8221; modes.', 'amp' )
					),
					'position' => array(
						'edge'  => 'left',
						'align' => 'middle',
					),
				),
			),
		);
	}
}
