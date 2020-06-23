( function( { ampSlug, disabledCookieName, userAgents } ) {
	const regExp = userAgents
		// Escape each user agent string before forming the regex expression.
		.map( function( userAgent ) {
			// See https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Regular_Expressions#Escaping.
			return userAgent.replace( /[.*+\-?^${}()|[\]\\]/g, '\\$&' ); // $& means the whole matched string
		} )
		.join( '|' );
	const re = new RegExp( regExp );
	const isMobile = re.test( navigator.userAgent );

	if ( isMobile ) {
		document.addEventListener( 'DOMContentLoaded', function() {
			// Show the mobile version switcher link once the DOM has loaded.
			const siteVersionSwitcher = document.getElementById( 'site-version-switcher' );
			if ( siteVersionSwitcher ) {
				siteVersionSwitcher.hidden = false;
			}
		} );
	}

	const mobileRedirectionDisabled = document.cookie
		.split( ';' )
		.some( function( item ) {
			return ( disabledCookieName + '=1' ) === item.trim();
		} );

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
