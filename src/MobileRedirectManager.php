<?php
/**
 * Class MobileRedirect.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AMP_Options_Manager;

/**
 * Centralized manager to handle mobile redirection of a page.
 *
 * @package AmpProject\AmpWP
 */
final class MobileRedirectManager {

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
	private static $disabled_cookie_is_set = false;

	/**
	 * Get whether mobile redirection is enabled or not.
	 *
	 * @return bool If JS redirection is disabled, only the status of the mobile redirection option is returned.
	 *              Otherwise, returns true if mobile redirection option is enabled and current request is from a mobile device.
	 */
	public static function is_enabled() {
		if ( ! AMP_Options_Manager::get_option( Option::MOBILE_REDIRECT ) ) {
			return false;
		}
		return self::should_redirect_via_js() || self::is_mobile_request();
	}

	/**
	 * Determine if the current request is from a mobile device.
	 *
	 * @return bool True if current request is from a mobile device, otherwise false.
	 */
	public static function is_mobile_request() {
		/**
		 * Filters whether the current request is from a mobile device. This is provided as a means to short-circuit
		 * the normal determination of a mobile request below.
		 *
		 * @since 1.6
		 *
		 * @param bool $is_mobile Whether the current request is from a mobile device.
		 */
		$pre_is_mobile = apply_filters( 'amp_pre_is_mobile', false );

		if ( true === $pre_is_mobile ) {
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
				self::get_user_agents()
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
	public static function should_redirect_via_js() {
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
	public static function get_user_agents() {
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
	 * Determine if mobile redirection is disabled for the browser session.
	 *
	 * @return bool True if disabled, false otherwise.
	 */
	public static function redirection_disabled_for_session() {
		return ( isset( $_COOKIE[ self::DISABLED_COOKIE_NAME ] ) && '1' === $_COOKIE[ self::DISABLED_COOKIE_NAME ] ) || self::$disabled_cookie_is_set;
	}

	/**
	 * Sets a cookie to disable mobile redirection for the current browser session.
	 *
	 * @return void
	 */
	public static function disable_redirect_for_session() {
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

		self::$disabled_cookie_is_set = true;
	}

	/**
	 * Output the mobile redirection Javascript code.
	 */
	public static function add_mobile_redirect_script() {
		?>
		<script>
			(function ( ampSlug, disabledCookieName, userAgents ) {
				var regExp = userAgents
						// Escape each user agent string before forming the regex expression.
						.map( function ( userAgent ) {
							// See https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Regular_Expressions#Escaping.
							return userAgent.replace(/[.*+\-?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
						} )
						.join( '|' );
				var re = new RegExp( regExp );
				var isMobile = re.test( navigator.userAgent );

				if ( isMobile ) {
					document.addEventListener( 'DOMContentLoaded', function () {
						// Show the mobile version switcher link once the DOM has loaded.
						var siteVersionSwitcher = document.getElementById( 'site-version-switcher' );
						if ( siteVersionSwitcher ) {
							siteVersionSwitcher.hidden = false;
						}
					} );
				}

				var mobileRedirectionDisabled = document.cookie
						.split(';')
						.some( ( item ) => `${ disabledCookieName }=1` === item.trim() );

				// Short-circuit if mobile redirection is disabled.
				if ( mobileRedirectionDisabled ) {
					return;
				}

				var url = new URL( location.href );
				if ( isMobile && ! url.searchParams.has( ampSlug ) ) {
					window.stop(); // Stop loading the page! This should cancel all loading resources.

					// Replace the current page with the AMP version.
					url.searchParams.append( ampSlug, '1' );
					location.replace( url.href );
				}
			} )(
				<?php echo wp_json_encode( amp_get_slug() ); ?>,
				<?php echo wp_json_encode( self::DISABLED_COOKIE_NAME ); ?>,
				<?php echo wp_json_encode( self::get_user_agents() ); ?>
			)
		</script>
		<?php
	}

	/**
	 * Output the markup for the mobile version switcher.
	 *
	 * @param bool   $is_amp Modifies markup to be AMP compatible if true.
	 * @param string $url    URL to canonical version of page.
	 * @param string $text   Text for the anchor element.
	 */
	public static function add_mobile_version_switcher_markup( $is_amp, $url, $text ) {
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
		<div id="site-version-switcher" <?php printf( ! $is_amp && self::should_redirect_via_js() ? 'hidden' : '' ); ?>>
			<a
				id="version-switch-link"
				rel="<?php printf( esc_attr( $is_amp ? 'noamphtml' : 'amphtml' ) ); ?> nofollow"
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
