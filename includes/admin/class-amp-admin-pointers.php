<?php
/**
 * Class AMP_Admin_Pointers
 *
 * @package AMP
 * @since 1.2
 */

/**
 * Class managing admin pointers to enhance discoverability.
 *
 * @since 1.2
 */
class AMP_Admin_Pointers {

	/**
	 * Registers functionality through WordPress hooks.
	 *
	 * @since 1.2
	 */
	public function init() {
		add_action(
			'admin_enqueue_scripts',
			[ $this, 'enqueue_scripts' ]
		);
	}

	/**
	 * Initializes admin pointers by enqueuing necessary scripts.
	 *
	 * @since 1.2
	 *
	 * @param string $hook_suffix The current admin screen hook suffix.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		$pointers = $this->get_pointers();
		if ( empty( $pointers ) ) {
			return;
		}

		// Only enqueue one pointer at a time to prevent them overlaying each other.
		foreach ( $pointers as $pointer ) {
			if ( ! $pointer->is_active( $hook_suffix ) ) {
				continue;
			}

			$pointer->enqueue();
			return;
		}
	}

	/**
	 * Gets available admin pointers.
	 *
	 * @since 1.2
	 *
	 * @return array List of AMP_Admin_Pointer instances.
	 */
	private function get_pointers() {
		return [
			new AMP_Admin_Pointer(
				'amp_template_mode_pointer_10',
				[
					'selector'        => '#toplevel_page_amp-options',
					'heading'         => esc_html__( 'AMP', 'amp' ),
					'subheading'      => esc_html__( 'New AMP Template Modes', 'amp' ),
					'description'     => esc_html__( 'You can now reuse your theme\'s templates and styles in AMP responses, in both &#8220;Transitional&#8221; and &#8220;Standard&#8221; modes.', 'amp' ),
					'position'        => [
						'align' => 'middle',
					],
					'active_callback' => static function() {
						return version_compare( strtok( AMP__VERSION, '-' ), '1.1', '<' );
					},
				]
			),
		];
	}
}
