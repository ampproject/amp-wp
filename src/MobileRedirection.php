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
use AmpProject\Attribute;

/**
 * Service for redirecting mobile users to the AMP version of a page.
 *
 * @package AmpProject\AmpWP
 */
final class MobileRedirection implements Service, Registerable {

	/**
	 * Regular expression for regular expressions. So meta.
	 *
	 * This must work in both PHP and cross-browser JS, which is why the 's' flag is not used. Also, this will get
	 * passed as the pattern argument to the RegExp constructor in JS, whereas in PHP it will be used as the pattern
	 * surrounded by the '#' delimiter.
	 *
	 * @var string
	 */
	const REGEX_REGEX = '^\/((?:.|\n)+)\/([i]*)$';

	/**
	 * The name of the cookie or session storage key that persists the user's preference for viewing the non-AMP version of a page when on mobile.
	 *
	 * @var string
	 */
	const DISABLED_STORAGE_KEY = 'amp_mobile_redirect_disabled';

	/**
	 * Register.
	 */
	public function register() {
		add_filter( 'amp_default_options', [ $this, 'filter_default_options' ] );
		add_action( 'amp_options_menu_items', [ $this, 'add_settings_field' ], 10 );
		add_filter( 'amp_options_updating', [ $this, 'sanitize_options' ], 10, 2 );

		if ( AMP_Options_Manager::get_option( Option::MOBILE_REDIRECT ) ) {
			add_action( 'wp', [ $this, 'redirect' ] );
		}
	}

	/**
	 * Add default option.
	 *
	 * @param array $defaults Default options.
	 * @return array Defaults.
	 */
	public function filter_default_options( $defaults ) {
		$defaults[ Option::MOBILE_REDIRECT ] = false;
		return $defaults;
	}

	/**
	 * Add settings field.
	 */
	public function add_settings_field() {
		add_settings_field(
			Option::MOBILE_REDIRECT,
			__( 'Mobile Redirection', 'amp' ),
			[ $this, 'render_setting_field' ],
			AMP_Options_Manager::OPTION_NAME,
			'general',
			[
				'class' => 'amp-mobile-redirect',
			]
		);
	}

	/**
	 * Render mobile redirect setting.
	 */
	public function render_setting_field() {
		?>
		<p>
			<label for="mobile_redirect">
				<input id="mobile_redirect" type="checkbox" name="<?php echo esc_attr( AMP_Options_Manager::OPTION_NAME . '[mobile_redirect]' ); ?>" <?php checked( AMP_Options_Manager::get_option( Option::MOBILE_REDIRECT ) ); ?> value="true">
				<?php esc_html_e( 'Redirect mobile visitors to the AMP version of a page.', 'amp' ); ?>
			</label>
		</p>
		<script>
			( function( $ ) {
				const standardModeInput = $( '#theme_support_standard' );
				const templateModeInputs = $( 'input[type=radio][name="amp-options[theme_support]"]' );
				const mobileRedirectSetting = $( 'tr.amp-mobile-redirect' );

				function toggleMobileRedirectSetting( ) {
					mobileRedirectSetting.toggleClass( 'hidden', standardModeInput.prop( 'checked' ) )
				}

				templateModeInputs.on( 'change', toggleMobileRedirectSetting );
				toggleMobileRedirectSetting();
			} )( jQuery )
		</script>
		<?php
	}

	/**
	 * Sanitize options.
	 *
	 * @param array $options     Existing options with already-sanitized values for updating.
	 * @param array $new_options Unsanitized options being submitted for updating.
	 *
	 * @return array Sanitized options.
	 */
	public function sanitize_options( $options, $new_options ) {
		$options[ Option::MOBILE_REDIRECT ] = (
			isset( $new_options[ Option::MOBILE_REDIRECT ] )
			&&
			rest_sanitize_boolean( $new_options[ Option::MOBILE_REDIRECT ] )
		);
		return $options;
	}

	/**
	 * Get the AMP version of the current URL.
	 *
	 * @return string AMP URL.
	 */
	private function get_current_amp_url() {
		$url = add_query_arg( amp_get_slug(), '1', amp_get_current_url() );
		$url = remove_query_arg( QueryVars::NOAMP, $url );
		return $url;
	}

	/**
	 * Add redirection logic if available for request.
	 */
	public function redirect() {
		if ( amp_is_canonical() || ! is_amp_available() ) {
			return;
		}

		$js = $this->is_using_client_side_redirection();

		if ( ! $js ) {
			// If using server-side redirection, make sure that caches vary by user agent.
			if ( ! headers_sent() ) {
				header( 'Vary: User-Agent', false );
			}

			// Now abort if it's not an AMP page and the user agent is not mobile, since there won't be any redirection
			// to the AMP version and we don't need to show a footer link to go to the AMP version.
			if ( ! $this->is_mobile_request() && ! is_amp_endpoint() ) {
				return;
			}
		}

		// Print the mobile switcher styles.
		add_action( 'wp_head', [ $this, 'add_mobile_version_switcher_styles' ] );
		add_action( 'amp_post_template_head', [ $this, 'add_mobile_version_switcher_styles' ] );

		if ( ! is_amp_endpoint() ) {
			add_action( 'wp_head', [ $this, 'add_mobile_alternative_link' ] );
			if ( $js ) {
				// Add mobile redirection script.
				add_action( 'wp_head', [ $this, 'add_mobile_redirect_script' ], ~PHP_INT_MAX );
			} elseif ( ! $this->is_redirection_disabled_via_cookie() ) {
				if ( $this->is_redirection_disabled_via_query_param() ) {
					// Persist disabling mobile redirection for the session if redirection is disabled for the current request.
					$this->set_mobile_redirection_disabled_cookie( true );
				} else {
					// Redirect to the AMP version since is_mobile_request and redirection not disabled by cookie or query param.
					if ( wp_safe_redirect( $this->get_current_amp_url(), 302 ) ) {
						exit;
					}
				}
			}

			// Add a link to the footer to allow for navigation to the AMP version.
			add_action( 'wp_footer', [ $this, 'add_amp_mobile_version_switcher' ] );
		} else {
			if ( ! $js && $this->is_redirection_disabled_via_cookie() ) {
				$this->set_mobile_redirection_disabled_cookie( false );
			}

			// Enable AMP-to-AMP linking by default to avoid redirecting to AMP version when navigating.
			// A low priority is used so that sites can continue overriding this if they have done so.
			add_filter( 'amp_to_amp_linking_enabled', '__return_true', 0 );

			add_filter( 'amp_to_amp_linking_element_excluded', [ $this, 'filter_amp_to_amp_linking_element_excluded' ], 100, 2 );
			add_filter( 'amp_to_amp_linking_element_query_vars', [ $this, 'filter_amp_to_amp_linking_element_query_vars' ], 10, 2 );

			// Add a link to the footer to allow for navigation to the non-AMP version.
			add_action( 'amp_post_template_footer', [ $this, 'add_non_amp_mobile_version_switcher' ] ); // For Classic reader mode theme.
			add_action( 'wp_footer', [ $this, 'add_non_amp_mobile_version_switcher' ] );
		}
	}

	/**
	 * Ensure that links/forms which point to ?noamp up-front are excluded from AMP-to-AMP linking.
	 *
	 * @param bool   $excluded Excluded.
	 * @param string $url      URL considered for exclusion.
	 * @return bool Element excluded from AMP-to-AMP linking.
	 */
	public function filter_amp_to_amp_linking_element_excluded( $excluded, $url ) {
		if ( ! $excluded ) {
			$query_string = wp_parse_url( $url, PHP_URL_QUERY );
			if ( ! empty( $query_string ) ) {
				$query_vars = [];
				parse_str( $query_string, $query_vars );
				$excluded = array_key_exists( QueryVars::NOAMP, $query_vars );
			}
		}
		return $excluded;
	}

	/**
	 * Ensure that links/forms which point to ?noamp up-front are excluded from AMP-to-AMP linking.
	 *
	 * @param string[] $query_vars Query vars.
	 * @param bool     $excluded   Whether the element was excluded from AMP-to-AMP linking.
	 * @return string[] Query vars to add to the element.
	 */
	public function filter_amp_to_amp_linking_element_query_vars( $query_vars, $excluded ) {
		if ( $excluded ) {
			$query_vars[ QueryVars::NOAMP ] = QueryVars::NOAMP_MOBILE;
		}
		return $query_vars;
	}

	/**
	 * Output the markup that allows the user to switch to the non-AMP version of the page.
	 */
	public function add_non_amp_mobile_version_switcher() {
		$url = add_query_arg( QueryVars::NOAMP, QueryVars::NOAMP_MOBILE, amp_remove_endpoint( amp_get_current_url() ) );
		$this->add_mobile_version_switcher_markup( true, $url, __( 'Exit mobile version', 'amp' ) );
	}

	/**
	 * Output the markup that allows the user to switch to the AMP version of the page.
	 */
	public function add_amp_mobile_version_switcher() {
		$url = $this->get_current_amp_url();
		$this->add_mobile_version_switcher_markup( false, $url, __( 'Go to mobile version', 'amp' ) );
	}

	/**
	 * Determine if the current request is from a mobile device by looking at the User-Agent request header.
	 *
	 * This only applies if client-side redirection has been disabled.
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

		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return false;
		}

		$current_user_agent = wp_unslash( $_SERVER['HTTP_USER_AGENT'] );
		$regex_regex        = sprintf( '#%s#', self::REGEX_REGEX );
		foreach ( $this->get_mobile_user_agents() as $user_agent_pattern ) {
			if (
				(
					preg_match( $regex_regex, $user_agent_pattern ) // So meta!
					&&
					preg_match( $user_agent_pattern, $current_user_agent )
				)
				||
				false !== strpos( $current_user_agent, $user_agent_pattern )
			) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if mobile redirection should be done via JavaScript.
	 *
	 * @return bool True if mobile redirection should be done, false otherwise.
	 */
	public function is_using_client_side_redirection() {
		/**
		 * Filters whether mobile redirection should be done client-side (via JavaScript).
		 *
		 * If false, a server-side solution will be used instead (via PHP). It's important to verify that server-side
		 * redirection does not conflict with a site's page caching logic. To assist with this, you may need to hook
		 * into the `amp_pre_is_mobile` filter.
		 *
		 * Beware that disabling this will result in a cookie being set when the user decides to leave the mobile version.
		 * This may require updating the site's privacy policy or getting user consent for GDPR compliance. Nevertheless,
		 * since the cookie is not used for tracking this may not be necessary.
		 *
		 * @since 1.6
		 *
		 * @param bool $should_redirect_via_js Whether JS redirection should be used to take mobile visitors to the AMP version.
		 */
		return (bool) apply_filters( 'amp_mobile_client_side_redirection', true );
	}

	/**
	 * Get a list of mobile user agents to use for comparison against the user agent from the current request.
	 *
	 * Each entry may either be a simple string needle, or it be a regular expression serialized as a string in the form
	 * of `/pattern/[i]*`. If a user agent string does not match this pattern, then the string will be used as a simple
	 * string needle for the haystack.
	 *
	 * @return string[] An array of mobile user agent search strings (and regex patterns).
	 */
	public function get_mobile_user_agents() {
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
		 * @param string[] $user_agents List of mobile user agent search strings (and regex patterns).
		 */
		return apply_filters( 'amp_mobile_user_agents', $default_user_agents );
	}

	/**
	 * Determine if mobile redirection is disabled via query param.
	 *
	 * @return bool True if disabled, false otherwise.
	 */
	public function is_redirection_disabled_via_query_param() {
		return isset( $_GET[ QueryVars::NOAMP ] ) && QueryVars::NOAMP_MOBILE === wp_unslash( $_GET[ QueryVars::NOAMP ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Determine if mobile redirection is disabled via cookie.
	 *
	 * @return bool True if disabled, false otherwise.
	 */
	public function is_redirection_disabled_via_cookie() {
		return isset( $_COOKIE[ self::DISABLED_STORAGE_KEY ] );
	}

	/**
	 * Sets a cookie to disable/enable mobile redirection for the current browser session.
	 *
	 * @param bool $add Whether to add (true) or remove (false) the cookie.
	 * @return void
	 */
	private function set_mobile_redirection_disabled_cookie( $add ) {
		if ( $add ) {
			$value   = '1';
			$expires = 0; // Time till expiry. Setting it to `0` means the cookie will only last for the current browser session.
		} else {
			$value   = null;
			$expires = time() - YEAR_IN_SECONDS;
		}

		$path     = wp_parse_url( home_url( '/' ), PHP_URL_PATH ); // Path.
		$secure   = is_ssl();                                      // Whether cookie should be transmitted over a secure HTTPS connection.
		$httponly = true;                                          // Access via JS is unnecessary since cookie only get/set via PHP.
		$samesite = 'strict';                                      // Prevents the cookie from being sent by the browser to the target site in all cross-site browsing context.
		$domain   = COOKIE_DOMAIN;

		// Pre PHP 7.3, the `samesite` cookie attribute had to be set via unconventional means. This was
		// addressed in PHP 7.3 (see <https://github.com/php/php-src/commit/5cb825df7251aeb28b297f071c35b227a3949f01>),
		// which now allows setting the cookie attribute via an options array.
		if ( 70300 <= PHP_VERSION_ID ) {
			setcookie(
				self::DISABLED_STORAGE_KEY,
				$value,
				compact( 'expires', 'path', 'secure', 'httponly', 'samesite', 'domain' )
			);
		} else {
			setcookie(
				self::DISABLED_STORAGE_KEY,
				$value,
				$expires,
				$path . ';samesite=' . $samesite, // Includes the samesite option as a hack to be set in the cookie. See <https://stackoverflow.com/a/46971326>.
				$domain,
				$secure,
				$httponly
			);
		}
	}

	/**
	 * Output the mobile redirection Javascript code.
	 */
	public function add_mobile_redirect_script() {
		$source = file_get_contents( __DIR__ . '/../assets/js/mobile-redirection.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions

		$exports = [
			'ampUrl'             => $this->get_current_amp_url(),
			'noampQueryVarName'  => QueryVars::NOAMP,
			'noampQueryVarValue' => QueryVars::NOAMP_MOBILE,
			'disabledStorageKey' => self::DISABLED_STORAGE_KEY,
			'mobileUserAgents'   => $this->get_mobile_user_agents(),
			'regexRegex'         => self::REGEX_REGEX,
		];

		$source = preg_replace( '/\bAMP_MOBILE_REDIRECTION\b/', wp_json_encode( $exports ), $source );

		printf( '<script>%s</script>', $source ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Add rel=alternate link for AMP version.
	 *
	 * @link https://developers.google.com/search/mobile-sites/mobile-seo/separate-urls#annotation-in-the-html
	 */
	public function add_mobile_alternative_link() {
		printf(
			'<link rel="alternate" type="text/html" media="only screen and (max-width: 640px)" href="%s">',
			esc_url( $this->get_current_amp_url() )
		);
	}

	/**
	 * Print the styles for the mobile version switcher.
	 */
	public function add_mobile_version_switcher_styles() {
		/**
		 * Filters whether the default mobile version switcher styles are printed.
		 *
		 * @since 1.6
		 *
		 * @param bool $used Whether the styles are printed.
		 */
		if ( ! apply_filters( 'amp_mobile_version_switcher_styles_used', true ) ) {
			return;
		}
		$source = file_get_contents( __DIR__ . '/../assets/css/amp-mobile-version-switcher' . ( is_rtl() ? '-rtl' : '' ) . '.css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		printf( '<style>%s</style>', $source ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Output the markup for the mobile version switcher.
	 *
	 * @param bool   $is_amp Modifies markup to be AMP compatible if true.
	 * @param string $url    URL to canonical version of page.
	 * @param string $text   Text for the anchor element.
	 */
	public function add_mobile_version_switcher_markup( $is_amp, $url, $text ) {
		$should_redirect_via_js = $this->is_using_client_side_redirection();

		if ( $is_amp ) {
			$rel = [ Attribute::REL_NOAMPHTML, Attribute::REL_NOFOLLOW ];
		} else {
			$rel = [ Attribute::REL_AMPHTML ];
		}

		$hide_switcher = (
			// The switcher must always be shown in the AMP version to allow accessing the non-AMP version.
			! $is_amp
			&&
			// The switcher should be hidden if using client-side redirection since JS will determine if it is a mobile
			// device and thus whether the switcher should be displayed.
			$should_redirect_via_js
		);
		?>
		<div id="amp-mobile-version-switcher" <?php printf( $hide_switcher ? 'hidden' : '' ); ?>>
			<a rel="<?php echo esc_attr( implode( ' ', $rel ) ); ?>" href="<?php echo esc_url( $url ); ?>">
				<?php echo esc_html( $text ); ?>
			</a>
		</div>
		<?php
	}
}
