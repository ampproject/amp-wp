<?php
/**
 * Class AMP_WP_Styles
 *
 * @package AMP
 */

/**
 * Extend WP_Styles with handling of CSS for AMP.
 *
 * @since 0.7
 */
class AMP_WP_Styles extends WP_Styles {

	/**
	 * Concatenation is always enabled for AMP.
	 *
	 * @since 0.7
	 * @var bool
	 */
	public $do_concat = true;

	/**
	 * Whether the locale stylesheet was done.
	 *
	 * @since 0.7
	 * @var bool
	 */
	protected $did_locale_stylesheet = false;

	/**
	 * Whether the Custom CSS was done.
	 *
	 * @since 0.7
	 * @var bool
	 */
	protected $did_custom_css = false;

	/**
	 * Regex for allowed font stylesheet URL.
	 *
	 * @var string
	 */
	protected $allowed_font_src_regex;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		$spec_name = 'link rel=stylesheet for fonts'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		foreach ( AMP_Allowed_Tags_Generated::get_allowed_tag( 'link' ) as $spec_rule ) {
			if ( isset( $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) && $spec_name === $spec_rule[ AMP_Rule_Spec::TAG_SPEC ]['spec_name'] ) {
				$this->allowed_font_src_regex = '@^(' . $spec_rule[ AMP_Rule_Spec::ATTR_SPEC_LIST ]['href']['value_regex'] . ')$@';
				break;
			}
		}
	}

	/**
	 * Generates an enqueued style's fully-qualified file path.
	 *
	 * @since 0.7
	 * @see WP_Styles::_css_href()
	 *
	 * @param string $src The source URL of the enqueued style.
	 * @param string $handle The style's registered handle.
	 * @return string|WP_Error Style's absolute validated filesystem path, or WP_Error when error.
	 */
	public function get_validated_css_file_path( $src, $handle ) {
		$needs_base_url = (
			! is_bool( $src )
			&&
			! preg_match( '|^(https?:)?//|', $src )
			&&
			! ( $this->content_url && 0 === strpos( $src, $this->content_url ) )
		);
		if ( $needs_base_url ) {
			$src = $this->base_url . $src;
		}

		/** This filter is documented in wp-includes/class.wp-styles.php */
		$src = apply_filters( 'style_loader_src', $src, $handle );
		$src = esc_url_raw( $src );

		// Strip query and fragment from URL.
		$src = preg_replace( ':[\?#].*$:', '', $src );

		if ( ! preg_match( '/\.(css|less|scss|sass)$/i', $src ) ) {
			/* translators: %1$s is stylesheet handle, %2$s is stylesheet URL */
			return new WP_Error( 'amp_css_bad_file_extension', sprintf( __( 'Skipped stylesheet %1$s which does not have recognized CSS file extension (%2$s).', 'amp' ), $handle, $src ) );
		}

		$includes_url = includes_url( '/' );
		$content_url  = content_url( '/' );
		$admin_url    = get_admin_url( null, '/' );
		$css_path     = null;
		if ( 0 === strpos( $src, $content_url ) ) {
			$css_path = WP_CONTENT_DIR . substr( $src, strlen( $content_url ) - 1 );
		} elseif ( 0 === strpos( $src, $includes_url ) ) {
			$css_path = ABSPATH . WPINC . substr( $src, strlen( $includes_url ) - 1 );
		} elseif ( 0 === strpos( $src, $admin_url ) ) {
			$css_path = ABSPATH . 'wp-admin' . substr( $src, strlen( $admin_url ) - 1 );
		}

		if ( ! $css_path || false !== strpos( '../', $css_path ) || 0 !== validate_file( $css_path ) || ! file_exists( $css_path ) ) {
			/* translators: %1$s is stylesheet handle, %2$s is stylesheet URL */
			return new WP_Error( 'amp_css_path_not_found', sprintf( __( 'Unable to locate filesystem path for stylesheet %1$s (%2$s).', 'amp' ), $handle, $src ) );
		}

		return $css_path;
	}

	/**
	 * Processes a style dependency.
	 *
	 * @since 0.7
	 * @see WP_Styles::do_item()
	 *
	 * @param string $handle The style's registered handle.
	 * @return bool True on success, false on failure.
	 */
	public function do_item( $handle ) {
		if ( ! WP_Dependencies::do_item( $handle ) ) {
			return false;
		}
		$obj = $this->registered[ $handle ];

		// Conditional styles and alternate styles aren't supported in AMP.
		if ( isset( $obj->extra['conditional'] ) || isset( $obj->extra['alt'] ) ) {
			return false;
		}

		if ( isset( $obj->args ) ) {
			$media = esc_attr( $obj->args );
		} else {
			$media = 'all';
		}

		// A single item may alias a set of items, by having dependencies, but no source.
		if ( ! $obj->src ) {
			$inline_style = $this->print_inline_style( $handle, false );
			if ( $inline_style ) {
				$this->print_code .= $inline_style;
			}
			return true;
		}

		// Allow font URLs.
		if ( $this->allowed_font_src_regex && preg_match( $this->allowed_font_src_regex, $obj->src ) ) {
			$this->do_concat = false;
			$result          = parent::do_item( $handle );
			$this->do_concat = true;
			return $result;
		}

		$css_file_path = $this->get_validated_css_file_path( $obj->src, $handle );
		if ( is_wp_error( $css_file_path ) ) {
			trigger_error( esc_html( $css_file_path->get_error_message() ), E_USER_WARNING ); // phpcs:ignore
			return false;
		}
		$css_rtl_file_path = '';

		// Handle RTL styles.
		if ( 'rtl' === $this->text_direction && isset( $obj->extra['rtl'] ) && $obj->extra['rtl'] ) {
			if ( is_bool( $obj->extra['rtl'] ) || 'replace' === $obj->extra['rtl'] ) {
				$suffix            = isset( $obj->extra['suffix'] ) ? $obj->extra['suffix'] : '';
				$css_rtl_file_path = $this->get_validated_css_file_path(
					str_replace( "{$suffix}.css", "-rtl{$suffix}.css", $obj->src ),
					"$handle-rtl"
				);
			} else {
				$css_rtl_file_path = $this->get_validated_css_file_path( $obj->extra['rtl'], "$handle-rtl" );
			}

			if ( is_wp_error( $css_rtl_file_path ) ) {
				trigger_error( esc_html( $css_rtl_file_path->get_error_message() ), E_USER_WARNING ); // phpcs:ignore
				$css_rtl_file_path = null;
			} elseif ( 'replace' === $obj->extra['rtl'] ) {
				$css_file_path = null;
			}
		}

		// Load the CSS from the filesystem.
		foreach ( array_filter( array( $css_file_path, $css_rtl_file_path ) ) as $css_path ) {
			$css = file_get_contents( $css_path ); // phpcs:ignore -- It's a local filesystem path not a remote request.
			if ( 'all' !== $media ) {
				$css = sprintf( '@media %s { %s }', $media, $css );
			}
			$this->print_code .= $css;
		}

		// Add inline styles.
		$inline_style = $this->print_inline_style( $handle, false );
		if ( $inline_style ) {
			$this->print_code .= $inline_style;
		}

		return true;
	}

	/**
	 * Get the locale stylesheet if it exists.
	 *
	 * @since 0.7
	 * @see locale_stylesheet()
	 * @return bool Whether locale stylesheet was done.
	 */
	public function do_locale_stylesheet() {
		if ( $this->did_locale_stylesheet ) {
			return true;
		}

		$src = get_locale_stylesheet_uri();
		if ( ! $src ) {
			return false;
		}
		$path = $this->get_validated_css_file_path( $src, get_stylesheet() . '-' . get_locale() );
		if ( is_wp_error( $path ) ) {
			return false;
		}
		$this->print_code .= file_get_contents( $path ); // phpcs:ignore -- The path has been validated, and it is not a remote path.
		$this->did_locale_stylesheet = true;
		return true;
	}

	/**
	 * Append Customizer Custom CSS.
	 *
	 * @since 0.7
	 * @see wp_custom_css()
	 * @see wp_custom_css_cb()
	 * @return bool Whether locale Custom CSS was done.
	 */
	public function do_custom_css() {
		if ( $this->did_custom_css ) {
			return true;
		}

		$css = trim( wp_get_custom_css() );
		if ( ! $css ) {
			return false;
		}

		$this->print_code .= $css;

		$this->did_custom_css = true;
		return true;
	}
}
