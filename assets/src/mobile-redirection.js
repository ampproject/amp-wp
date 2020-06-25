( function( { ampSlug, disabledCookieName, mobileUserAgents, regexRegex } ) {
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

	if ( isMobile ) {
		document.addEventListener( 'DOMContentLoaded', () => {
			// Show the mobile version switcher link once the DOM has loaded.
			const siteVersionSwitcher = document.getElementById( 'site-version-switcher' );
			if ( siteVersionSwitcher ) {
				siteVersionSwitcher.hidden = false;
			}
		} );
	}

	const mobileRedirectionDisabled = document.cookie
		.split( ';' )
		.some( ( item ) => disabledCookieName + '=1' === item.trim() );

	// Short-circuit if mobile redirection is disabled.
	if ( mobileRedirectionDisabled ) {
		return;
	}

	const url = new URL( location.href );
	if ( isMobile && ! url.searchParams.has( ampSlug ) ) {
		window.stop(); // Stop loading the page! This should cancel all loading resources.

		// Replace the current page with the AMP version.
		url.searchParams.append( ampSlug, '1' );
		location.replace( url.href );
	}
}(
	// Note: The argument here is replaced with JSON in PHP by \AmpProject\AmpWP\MobileRedirection::add_mobile_redirect_script().
	AMP_MOBILE_REDIRECTION,
) );
