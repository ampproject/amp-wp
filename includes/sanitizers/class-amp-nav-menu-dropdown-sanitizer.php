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
	 * Default args.
	 *
	 * @since 1.1.0
	 * @var array
	 */
	protected $DEFAULT_ARGS = array(
		'sub_menu_button_class'        => '',
		'sub_menu_button_toggle_class' => '',
		'expand_text'                  => '',
		'collapse_text'                => '',
		'icon'                         => null, // Optional.
		'sub_menu_item_state_id'       => 'navMenuItemExpanded',
	);

	/**
	 * AMP_Nav_Menu_Dropdown_Sanitizer constructor.
	 *
	 * @since 1.1.0
	 *
	 * @param DOMDocument $dom  DOM.
	 * @param array       $args Args.
	 */
	public function __construct( $dom, $args = array() ) {
		parent::__construct( $dom, $args );

		$this->args = self::ensure_defaults( $this->args );
	}

	/**
	 * Add filter to manipulate output during output buffering to add AMP-compatible dropdown toggles.
	 *
	 * @since 1.0
	 *
	 * @param array $args Args.
	 */
	public static function add_buffering_hooks( $args = array() ) {
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
			function( $item_output, $item, $depth, $nav_menu_args ) use ( $args ) {
				unset( $depth );

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
				if ( isset( $args['expand_text'] ) && isset( $args['collapse_text'] ) ) {
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

	/**
	 * Ensure that some defaults are always set as fallback.
	 *
	 * @param array $args Arguments to set the defaults in as necessary.
	 * @return array Arguments with defaults filled.
	 */
	protected static function ensure_defaults( $args ) {
		// Ensure accessibility labels are always set.
		if ( empty( $args['expand_text'] ) ) {
			$args['expand_text'] = __( 'expand child menu', 'amp' );
		}
		if ( empty( $args['collapse_text'] ) ) {
			$args['collapse_text'] = __( 'collapse child menu', 'amp' );
		}

		// Ensure the state ID is always set.
		if ( empty( $args['nav_menu_item_state_id'] ) ) {
			$args['nav_menu_item_state_id'] = 'navMenuItemExpanded';
		}

		return $args;
	}
}
