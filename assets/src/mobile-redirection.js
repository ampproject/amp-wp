( function( { ampUrl, noampQueryVar, disabledStorageKey, mobileUserAgents, regexRegex } ) {
	if ( typeof sessionStorage === 'undefined' ) {
		return;
	}

	const regExpRegExp = new RegExp( regexRegex );

	// A user agent may be expressed as a RegExp string serialization in the the form of `/pattern/[i]*`. If a user
	// agent string does not match this pattern, then the string will be used as a simple string needle for the haystack.
	const isMobile = mobileUserAgents.some( ( pattern ) => {
		const matches = pattern.match( regExpRegExp ); // A regex for a regex. So meta.
		if ( matches ) {
			const re = new RegExp( matches[ 1 ], matches[ 2 ] );
			if ( re.test( navigator.userAgent ) ) {
				return true;
			}
		}
		return navigator.userAgent.includes( pattern );
	} );

	// If not mobile, there's nothing left to do.
	if ( ! isMobile ) {
		return;
	}

	document.addEventListener( 'DOMContentLoaded', () => {
		// Show the mobile version switcher link once the DOM has loaded.
		const siteVersionSwitcher = document.getElementById( 'amp-mobile-version-switcher' );
		if ( siteVersionSwitcher ) {
			siteVersionSwitcher.hidden = false;
		}
	} );

	// Short-circuit if mobile redirection is disabled.
	if ( sessionStorage.getItem( disabledStorageKey ) ) {
		return;
	}

	const url = new URL( location.href );

	if ( url.searchParams.has( noampQueryVar ) ) {
		// If the noamp query param is present, remember that redirection should be disabled.
		sessionStorage.setItem( disabledStorageKey, '1' );
	} else {
		// Otherwise, since JS is running then we know it's not an AMP page and we need to redirect to the AMP version.
		window.stop(); // Stop loading the page! This should cancel all loading resources.

		// Replace the current page with the AMP version.
		location.replace( ampUrl );
	}
}(
	// Note: The argument here is replaced with JSON in PHP by \AmpProject\AmpWP\MobileRedirection::add_mobile_redirect_script().
	AMP_MOBILE_REDIRECTION,
) );
