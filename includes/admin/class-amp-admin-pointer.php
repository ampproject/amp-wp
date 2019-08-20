<?php
/**
 * Class AMP_Admin_Pointer
 *
 * @package AMP
 * @since 1.2
 */

/**
 * Class representing a single admin pointer.
 *
 * @since 1.2
 */
class AMP_Admin_Pointer {

	/**
	 * Unique pointer slug.
	 *
	 * @since 1.2
	 * @var string
	 */
	private $slug;

	/**
	 * Pointer arguments.
	 *
	 * @since 1.2
	 * @var array
	 */
	private $args;

	/**
	 * Internal storage for dismissed pointers, to prevent repeated parsing.
	 *
	 * @since 1.2
	 * @static
	 * @var array|null
	 */
	private static $dismissed_pointers;

	/**
	 * Constructor.
	 *
	 * @since 1.2
	 *
	 * @param string $slug Unique pointer slug.
	 * @param array  $args {
	 *     Associative array of pointer arguments.
	 *
	 *     @type string   $selector        Required DOM selector for the element to point at.
	 *     @type string   $description     Required pointer description. May contain inline HTML tags.
	 *     @type string   $heading         Pointer heading. May contain inline HTML tags. Default empty string.
	 *     @type string   $subheading      Pointer subheading. May contain inline HTML tags. Default empty string.
	 *     @type array    $position        Position information for the pointer. Must be an array with keys
	 *                                     'edge' and 'align'. Default is 'edge' set to 'left' and 'align' set
	 *                                     to 'bottom'.
	 *     @type string   $class           Additional CSS class for the pointer. Default empty string.
	 *     @type callable $active_callback Callback function to determine whether the pointer is active in the
	 *                                     current context. The current admin screen's hook suffix is passed to
	 *                                     the callback. Default is that the pointer is active unconditionally.
	 * }
	 */
	public function __construct( $slug, array $args ) {
		$default_position = [
			'edge'  => is_rtl() ? 'right' : 'left',
			'align' => 'bottom',
		];

		if ( isset( $args['position'] ) ) {
			$args['position'] = wp_parse_args( (array) $args['position'], $default_position );
		}

		$this->slug = $slug;
		$this->args = wp_parse_args(
			$args,
			[
				'selector'        => '',
				'description'     => '',
				'heading'         => '',
				'subheading'      => '',
				'position'        => $default_position,
				'class'           => '',
				'active_callback' => null,
			]
		);
	}

	/**
	 * Gets the pointer slug.
	 *
	 * @since 1.2
	 *
	 * @return string Unique pointer slug.
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Checks whether the pointer is active.
	 *
	 * This method executes the active callback and looks at whether the pointer has been dismissed in order to
	 * determine whether the pointer should be active or not.
	 *
	 * @since 1.2
	 *
	 * @param string $hook_suffix The current admin screen hook suffix.
	 * @return bool True if the pointer is active, false otherwise.
	 */
	public function is_active( $hook_suffix ) {
		if ( ! $this->args['selector'] || ! $this->args['description'] ) {
			return false;
		}

		if ( ! $this->args['active_callback'] ) {
			return true;
		}

		if ( ! call_user_func( $this->args['active_callback'], $hook_suffix ) ) {
			return false;
		}

		// Populate dismissed pointers list only once.
		if ( null === self::$dismissed_pointers ) {
			// Use array_flip() for more performant lookup.
			self::$dismissed_pointers = array_flip( explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) ) );
		}

		return ! isset( self::$dismissed_pointers[ $this->slug ] );
	}

	/**
	 * Enqueues the script for the pointer.
	 *
	 * @since 1.2
	 */
	public function enqueue() {
		wp_enqueue_style( 'wp-pointer' );
		wp_enqueue_script( 'wp-pointer' );

		wp_enqueue_style(
			'amp-validation-tooltips',
			amp_get_asset_url( 'css/amp-validation-tooltips.css' ),
			[ 'wp-pointer' ],
			AMP__VERSION
		);

		wp_styles()->add_data( 'amp-validation-tooltips', 'rtl', 'replace' );

		add_action(
			'admin_print_footer_scripts',
			function() {
				$this->print_js();
			}
		);
	}

	/**
	 * Prints the script for the pointer inline.
	 *
	 * Requires the 'wp-pointer' script to be loaded.
	 *
	 * @since 1.2
	 */
	private function print_js() {
		$content = '<p>' . wp_kses( $this->args['description'], 'amp_admin_pointer' ) . '</p>';
		if ( $this->args['subheading'] ) {
			$content = '<p><strong>' . wp_kses( $this->args['subheading'], 'amp_admin_pointer' ) . '</strong></p>' . $content;
		}
		if ( $this->args['heading'] ) {
			$content = '<h3>' . wp_kses( $this->args['heading'], 'amp_admin_pointer' ) . '</h3>' . $content;
		}

		$args = [
			'content'      => $content,
			'position'     => $this->args['position'],
			'pointerClass' => 'wp-pointer wp-amp-pointer' . ( ! empty( $this->args['class'] ) ? ' ' . $this->args['class'] : '' ),
		];

		?>
		<script type="text/javascript">
			( function( $ ) {
				var options = <?php echo wp_json_encode( $args ); ?>;

				if ( ! options ) {
					return;
				}

				options = $.extend( options, {
					close: function() {
						$.post( ajaxurl, {
							pointer: '<?php echo esc_js( $this->slug ); ?>',
							action: 'dismiss-wp-pointer'
						});
					}
				});

				function setup() {
					$( '<?php echo esc_js( $this->args['selector'] ); ?>' ).first().pointer( options ).pointer( 'open' );
				}
				if ( options.position && options.position.defer_loading ) {
					$( window ).bind( 'load.wp-pointers', setup );
				} else {
					$( document ).ready( setup );
				}

			} )( jQuery );
		</script>
		<?php
	}
}
