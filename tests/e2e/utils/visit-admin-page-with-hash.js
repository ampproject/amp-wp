/**
 * External dependencies
 */
import { join } from 'path';

/**
 * WordPress dependencies
 */
import { getPageError } from '@wordpress/e2e-test-utils';

function createURLWithHash( WPPath, query = '', hash = '' ) {
	const url = new URL( process.env.WP_BASE_URL );

	url.pathname = join( url.pathname, WPPath );
	url.search = query;
	url.hash = hash;

	return url.href;
}

/**
 * Visits admin page with hash option; if user is not logged in then it logging in it first, then visits admin page.
 *
 * @param {string} adminPath String to be serialized as pathname.
 * @param {string} query     String to be serialized as query portion of URL.
 * @param {string} hash      URL hash.
 */
export async function visitAdminPageWithHash( adminPath, query, hash = '' ) {
	await page.goto( createURLWithHash( join( 'wp-admin', adminPath ), query, hash ) );

	const error = await getPageError();
	if ( error ) {
		throw new Error( 'Unexpected error in page content: ' + error );
	}
}
