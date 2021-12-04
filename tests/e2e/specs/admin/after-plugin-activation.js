/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { completeWizard } from '../../utils/onboarding-wizard-utils';

describe( 'After plugin activation', () => {
	const timeout = 30000;

	async function deactivate( slug ) {
		await page.click( `tr[data-slug="${ slug }"] .deactivate a` );
		await page.waitForSelector( `tr[data-slug="${ slug }"] .delete a` );
	}

	async function activate( slug ) {
		await page.click( `tr[data-slug="${ slug }"] .activate a` );
		await page.waitForSelector( `tr[data-slug="${ slug }"] .deactivate a` );
	}

	beforeAll( async () => {
		await completeWizard( { mode: 'transitional' } );
		await visitAdminPage( 'plugins.php', '' );
	} );

	it( 'site scan is triggered automatically and displays no validation issues for AMP-compatible plugin', async () => {
		await deactivate( 'gutenberg' );

		await expect( page ).not.toMatchElement( '#amp-site-scan-notice' );

		await activate( 'gutenberg' );

		await expect( page ).toMatchElement( '#amp-site-scan-notice' );
		await expect( page ).toMatchElement( '#amp-site-scan-notice p', { text: /Checking your site for AMP compatibility issues/ } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice p', { text: /No AMP compatibility issues detected/, timeout } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .amp-admin-notice--success' );
		await expect( page ).not.toMatchElement( '#amp-site-scan-notice summary' );
		await expect( page ).not.toMatchElement( '#amp-site-scan-notice .amp-site-scan-notice__cta' );
	} );

	it.each( [
		'with Gutenberg active',
		'without Gutenberg active',
	] )( 'site scan is triggered automatically and displays validation issues for AMP-incompatible plugin %s', async ( title ) => {
		const withoutGutenberg = title.startsWith( 'without' );

		//eslint-disable-next-line jest/no-if
		if ( withoutGutenberg ) {
			await deactivate( 'gutenberg' );
		}

		await activate( 'e2e-tests-demo-plugin' );

		await expect( page ).toMatchElement( '#amp-site-scan-notice' );
		await expect( page ).toMatchElement( '#amp-site-scan-notice p', { text: /Checking your site for AMP compatibility issues/ } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice p', { text: /AMP compatibility issues discovered with the following plugin:/, timeout } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .amp-admin-notice--warning' );
		await expect( page ).toMatchElement( '#amp-site-scan-notice summary', { text: /E2E Tests Demo Plugin/ } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .button', { text: /Review Plugin Suppression/ } );
		await expect( page ).toMatchElement( '#amp-site-scan-notice .button', { text: /View AMP-Compatible Plugins/ } );

		await deactivate( 'e2e-tests-demo-plugin' );

		await expect( page ).not.toMatchElement( '#amp-site-scan-notice' );

		//eslint-disable-next-line jest/no-if
		if ( withoutGutenberg ) {
			await activate( 'gutenberg' );
		}
	} );
} );
