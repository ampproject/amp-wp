<?php
/**
 * Class MobileRedirection.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Service for redirecting mobile users to the AMP version of a page.
 *
 * @package AmpProject\AmpWP
 */
final class MobileRedirection implements Service, Registerable {

	/**
	 * The name of the cookie that persists the user's preference for viewing the non-AMP version of a page when on mobile.
	 *
	 * @var string
	 */
	const DISABLED_COOKIE_NAME = 'amp_mobile_redirect_disabled';

	/**
	 * Query parameter to indicate that the page in question should not be served as AMP.
	 *
	 * @var string
	 */
	const NO_AMP_QUERY_VAR = 'noamp';

	/**
	 * Indicates whether the the `amp_mobile_redirect_disabled` cookie has been set during the current request.
	 *
	 * When a cookie is set, it cannot be accessed via the `$_COOKIE` array until the next page load. This circumvent thia,
	 * whenever the cookie is set during the current request, this variable becomes true and can be used as a fallback to
	 * detect whether redirection has been disabled for the session.
	 *
	 * @var bool
	 */
	private $disabled_cookie_is_set;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->disabled_cookie_is_set = false;
	}

	/**
	 * Register.
	 */
	public function register() {
		add_action( 'wp', [ $this, 'redirect' ] );
		add_filter( 'amp_unavailable_redirect_url', [ $this, 'ensure_noamp_unavailable_redirect_url' ] );
	}

	/**
	 * Add redirection logic if available for request.
	 */
	public function redirect() {
		if ( ! $this->is_available_for_request() ) {
			return;
		}

		if ( ! is_amp_endpoint() ) {
			// Persist disabling mobile redirection for the session if redirection is disabled for the current request.
			if ( ! $this->redirection_disabled_for_session() && $this->redirection_disabled_for_request() ) {
				$this->disable_redirect_for_session();
			}

			// Redirect if mobile redirection is not disabled for the session and JS redirection is disabled.
			if ( ! $this->redirection_disabled_for_session() && ! $this->should_redirect_via_js() ) {
				if ( ! headers_sent() ) {
					header( 'Vary: User-Agent' ); // @todo This needs to not replace existing.
				}

				$amp_url = add_query_arg( amp_get_slug(), '1', amp_get_current_url() );
				wp_safe_redirect( $amp_url, 302 );
			}

			// Add mobile redirection script if user has opted for that solution.
			if ( $this->should_redirect_via_js() ) {
				// The redirect script will add the mobile version switcher link.
				add_action( 'wp_head', [ $this, 'add_mobile_redirect_script' ], ~PHP_INT_MAX );
			}

			// Add a link to the footer to allow for navigation to the AMP version.
			add_action( 'wp_footer', [ $this, 'add_amp_mobile_version_switcher' ] );
		} elseif ( ! amp_is_canonical() ) {
			// Add a link to the footer to allow for navigation to the non-AMP version.
			add_action( 'amp_post_template_footer', [ $this, 'add_non_amp_mobile_version_switcher' ] ); // For Classic reader mode theme.
			add_action( 'wp_footer', [ $this, 'add_non_amp_mobile_version_switcher' ] );
		}
	}

	/**
	 * Add the noamp query var when redirecting to the non-AMP version due to AMP not being available.
	 *
	 * This is to avoid the possibility of a mobile device endlessly redirecting between the AMP and non-AMP URLs.
	 *
	 * @param string $redirect_url Redirect URL.
	 * @return string Redirect URL.
	 */
	public function ensure_noamp_unavailable_redirect_url( $redirect_url ) {
		return add_query_arg( self::NO_AMP_QUERY_VAR, '1', $redirect_url );
	}

	/**
	 * Output the markup that allows the user to switch to the non-AMP version of the page.
	 */
	public function add_non_amp_mobile_version_switcher() {
		$url = add_query_arg( self::NO_AMP_QUERY_VAR, '1', AMP_Theme_Support::get_current_canonical_url() ); // @todo Just use amp_remove_endpoint( amp_get_current_url() ).
		$this->add_mobile_version_switcher_markup( true, $url, __( 'Exit mobile version', 'amp' ) );
	}

	/**
	 * Output the markup that allows the user to switch to the AMP version of the page.
	 */
	public function add_amp_mobile_version_switcher() {
		$amp_url = AMP_Theme_Support::is_paired_available()
			? add_query_arg( amp_get_slug(), '', amp_get_current_url() )
			: amp_get_permalink( get_queried_object_id() );
		$amp_url = remove_query_arg( self::NO_AMP_QUERY_VAR, $amp_url );

		$this->add_mobile_version_switcher_markup( false, $amp_url, __( 'Go to mobile version', 'amp' ) );
	}

	/**
	 * Get whether mobile redirection is enabled or not.
	 *
	 * @return bool If JS redirection is disabled, only the status of the mobile redirection option is returned.
	 *              Otherwise, returns true if mobile redirection option is enabled and current request is from a mobile device.
	 */
	public function is_enabled() {
		if ( ! AMP_Options_Manager::get_option( Option::MOBILE_REDIRECT ) ) {
			return false;
		}
		return $this->should_redirect_via_js() || $this->is_mobile_request();
	}

	/**
	 * Determine if mobile redirection is available for the current request.
	 *
	 * @return bool True if available, false otherwise.
	 */
	public function is_available_for_request() {
		return $this->is_enabled() && is_amp_available();
	}

	/**
	 * Determine if the current request is from a mobile device.
	 *
	 * @return bool True if current request is from a mobile device, otherwise false.
	 */
	public function is_mobile_request() {
		/**
		 * Filters whether the current request is from a mobile device. This is provided as a means to short-circuit
		 * the normal determination of a mobile request below.
		 *
		 * @since 1.6
		 *
		 * @param null $is_mobile Whether the current request is from a mobile device.
		 */
		$pre_is_mobile = apply_filters( 'amp_pre_is_mobile', null );

		if ( null !== $pre_is_mobile ) {
			return (bool) $pre_is_mobile;
		}

		$current_user_agent = wp_unslash( $_SERVER['HTTP_USER_AGENT'] );

		if ( empty( $current_user_agent ) ) {
			return false;
		}

		$user_agents_regex = implode(
			'|',
			array_map(
				static function ( $user_agent ) {
					return preg_quote( $user_agent, '/' );
				},
				$this->get_user_agents()
			)
		);

		if ( preg_match( "/$user_agents_regex/", $current_user_agent ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if mobile redirection should be done via JavaScript.
	 *
	 * @return bool True if mobile redirection should be done, false otherwise.
	 */
	public function should_redirect_via_js() {
		/**
		 * Filters whether mobile redirection should be done via JavaScript. If false, a server-side solution will be used instead.
		 *
		 * @since 1.6
		 *
		 * @param bool $should_redirect_via_js Whether JS redirection should be used.
		 */
		return (bool) apply_filters( 'amp_redirect_via_js', true );
	}

	/**
	 * Get a list of user agents to use for comparison against the user agent from the current request.
	 *
	 * @return string[] An array of user agents.
	 */
	public function get_user_agents() {
		// Default list compiled from the user agents listed in `wp_is_mobile()`.
		$default_user_agents = [
			'Mobile',
			'Android',
			'Silk/',
			'Kindle',
			'BlackBerry',
			'Opera Mini',
			'Opera Mobi',
		];

		/**
		 * Filters the list of user agents used to determine if the user agent from the current request is a mobile one.
		 *
		 * @since 1.6
		 *
		 * @param string[] $user_agents List of user agents.
		 */
		return apply_filters( 'amp_mobile_user_agents', $default_user_agents );
	}

	/**
	 * Determine if mobile redirection is disabled for the current request.
	 *
	 * @return bool True if disabled, false otherwise.
	 */
	public function redirection_disabled_for_request() {
		return ( isset( $_GET[ self::NO_AMP_QUERY_VAR ] ) && '1' === $_GET[ self::NO_AMP_QUERY_VAR ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Determine if mobile redirection is disabled for the browser session.
	 *
	 * @return bool True if disabled, false otherwise.
	 */
	public function redirection_disabled_for_session() {
		return ( isset( $_COOKIE[ self::DISABLED_COOKIE_NAME ] ) && '1' === $_COOKIE[ self::DISABLED_COOKIE_NAME ] ) || $this->disabled_cookie_is_set;
	}

	/**
	 * Sets a cookie to disable mobile redirection for the current browser session.
	 *
	 * @return void
	 */
	public function disable_redirect_for_session() {
		$value    = '1';                                           // Cookie value.
		$expires  = 0;                                             // Time till expiry. Setting it to `0` means the cookie will only last for the current browser session.
		$path     = wp_parse_url( home_url( '/' ), PHP_URL_PATH ); // Path.
		$domain   = $_SERVER['HTTP_HOST'];                         // Domain.
		$secure   = is_ssl();                                      // Whether cookie should be transmitted over a secure HTTPS connection.
		$httponly = false;                                         // Whether cookie should be made accessible only through the HTTP protocol.
		$samesite = 'strict';                                      // Prevents the cookie from being sent by the browser to the target site in all cross-site browsing context.

		// Pre PHP 7.3, the `samesite` cookie attribute had to be set via unconventional means. This was
		// addressed in PHP 7.3 (see <https://github.com/php/php-src/commit/5cb825df7251aeb28b297f071c35b227a3949f01>),
		// which now allows setting the cookie attribute via an options array.
		if ( 70300 <= PHP_VERSION_ID ) {
			setcookie(
				self::DISABLED_COOKIE_NAME,
				$value,
				compact( 'expires', 'path', 'domain', 'secure', 'httponly', 'samesite' )
			);
		} else {
			setcookie(
				self::DISABLED_COOKIE_NAME,
				$value,
				$expires,
				$path . ';samesite=' . $samesite, // Includes the samesite option as a hack to be set in the cookie. See <https://stackoverflow.com/a/46971326>.
				$domain,
				$secure,
				$httponly
			);
		}

		$this->disabled_cookie_is_set = true;
	}

	/**
	 * Output the mobile redirection Javascript code.
	 */
	public function add_mobile_redirect_script() {
		$source = file_get_contents( __DIR__ . '/../assets/js/mobile-redirection.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		// Inject global variables.
		$source  = preg_replace( '#/\*\s*global\s.+?\*/#', '', $source );
		$globals = [
			'AMP_SLUG'    => amp_get_slug(),
			'STORAGE_KEY' => self::DISABLED_COOKIE_NAME,
			'USER_AGENTS' => $this->get_user_agents(),
		];
		$source  = preg_replace_callback(
			sprintf( '/\b(%s)\b/', implode( '|', array_keys( $globals ) ) ),
			static function ( $matches ) use ( $globals ) {
				return wp_json_encode( $globals[ $matches[0] ] );
			},
			$source
		);

		printf( '<script>%s</script>', $source ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Output the markup for the mobile version switcher.
	 *
	 * @param bool   $is_amp Modifies markup to be AMP compatible if true.
	 * @param string $url    URL to canonical version of page.
	 * @param string $text   Text for the anchor element.
	 */
	public function add_mobile_version_switcher_markup( $is_amp, $url, $text ) {
		?>
		<style>
			#version-switch-link {
				display: block;
				width: 100%;
				padding: 15px 0;
				font-size: 16px;
				font-weight: 600;
				color: #eaeaea;
				text-align: center;
				background-color: #444;
				border: 0;
			}
		</style>
		<div id="site-version-switcher" <?php printf( ! $is_amp && $this->should_redirect_via_js() ? 'hidden' : '' ); ?>>
			<a
				id="version-switch-link"
				rel="<?php printf( esc_attr( $is_amp ? 'noamphtml nofollow' : 'amphtml' ) ); ?>"
				href="<?php echo esc_url( $url ); ?>"
				<?php
				if ( ! $is_amp ) {
					// Add `onclick` attribute to enable mobile redirection when the user clicks to go to the mobile version.
					printf(
						'onclick="%s"',
						esc_attr( 'document.cookie = "' . self::DISABLED_COOKIE_NAME . '=0;path=/;samesite=strict" + ( "https:" === location.protocol ? ";secure" : "" );' )
					);
				}
				?>
			>
				<?php echo esc_html( $text ); ?>
			</a>
		</div>
		<?php
	}
}
