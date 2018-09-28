<?php
/**
 * Class AMP_Nav_Menu_Dropdown_Sanitizer
 *
 * @package AMP
 */

/**
 * Class AMP_Nav_Menu_Dropdown_Sanitizer
 *
 * Handles state for navigation menu dropdown toggles, based on theme support.
 *
 * @since 1.1.0
 */
class AMP_Nav_Menu_Dropdown_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Get default args.
	 *
	 * @since 1.3
	 * @return array Default args.
	 */
	public static function get_default_args() {
		$args = [
			'sub_menu_button_class'        => '',
			'sub_menu_button_toggle_class' => '',
			'expand_text'                  => __( 'expand child menu', 'amp' ),
			'collapse_text'                => __( 'collapse child menu', 'amp' ),
			'icon'                         => null, // Optional.
			'sub_menu_item_state_id'       => 'navMenuItemExpanded',
		];

		$theme_support_args = AMP_Theme_Support::get_theme_support_args();
		if ( ! empty( $theme_support_args['nav_menu_dropdown'] ) ) {
			$args = array_merge( $args, $theme_support_args['nav_menu_dropdown'] );
		}

		return $args;
	}

	/**
	 * Add filter to manipulate output during output buffering to add AMP-compatible dropdown toggles.
	 *
	 * @since 1.0
	 *
	 * @param array $args Args.
	 */
	public static function add_buffering_hooks( $args = [] ) {
		if ( empty( $args['sub_menu_button_class'] ) || empty( $args['sub_menu_button_toggle_class'] ) ) {
			return;
		}

		$args = self::ensure_defaults( $args );

		/**
		 * Filter the HTML output of a nav menu item to add the AMP dropdown button to reveal the sub-menu.
		 *
		 * @param string $item_output Nav menu item HTML.
		 * @param object $item        Nav menu item.
		 * @return string Modified nav menu item HTML.
		 */
		add_filter(
			'walker_nav_menu_start_el',
			static function( $item_output, $item, $depth, $nav_menu_args ) use ( $args ) {
				// Skip adding buttons to nav menu widgets for now.
				if ( empty( $nav_menu_args->theme_location ) ) {
					return $item_output;
				}

				if ( ! in_array( 'menu-item-has-children', $item->classes, true ) ) {
					return $item_output;
				}

				static $nav_menu_item_number = 0;
				$nav_menu_item_number++;

				$expanded = in_array( 'current-menu-ancestor', $item->classes, true );

				$expanded_state_id = $args['nav_menu_item_state_id'] . $nav_menu_item_number;

				// Create new state for managing storing the whether the sub-menu is expanded.
				$item_output .= sprintf(
					'<amp-state id="%s"><script type="application/json">%s</script></amp-state>',
					esc_attr( $expanded_state_id ),
					wp_json_encode( $expanded )
				);

				$dropdown_button  = '<button';
				$dropdown_button .= sprintf(
					' class="%s" [class]="%s"',
					esc_attr( $args['sub_menu_button_class'] . ( $expanded ? ' ' . $args['sub_menu_button_toggle_class'] : '' ) ),
					esc_attr( sprintf( "%s + ( $expanded_state_id ? %s : '' )", wp_json_encode( $args['sub_menu_button_class'] ), wp_json_encode( ' ' . $args['sub_menu_button_toggle_class'] ) ) )
				);
				$dropdown_button .= sprintf(
					' aria-expanded="%s" [aria-expanded]="%s"',
					esc_attr( wp_json_encode( $expanded ) ),
					esc_attr( "$expanded_state_id ? 'true' : 'false'" )
				);
				$dropdown_button .= sprintf(
					' on="%s"',
					esc_attr( "tap:AMP.setState( { $expanded_state_id: ! $expanded_state_id } )" )
				);
				$dropdown_button .= '>';

				if ( isset( $args['icon'] ) ) {
					$dropdown_button .= $args['icon'];
				}
				if ( isset( $args['expand_text'], $args['collapse_text'] ) ) {
					$dropdown_button .= sprintf(
						'<span class="screen-reader-text" [text]="%s">%s</span>',
						esc_attr( sprintf( "$expanded_state_id ? %s : %s", wp_json_encode( $args['collapse_text'] ), wp_json_encode( $args['expand_text'] ) ) ),
						esc_html( $expanded ? $args['collapse_text'] : $args['expand_text'] )
					);
				}

				$dropdown_button .= '</button>';

				$item_output .= $dropdown_button;
				return $item_output;
			},
			10,
			4
		);
	}

	/**
	 * Method needs to be stubbed to fulfill base class requirements.
	 *
	 * @since 1.1.0
	 */
	public function sanitize() {
		// Empty method body.
	}
}
