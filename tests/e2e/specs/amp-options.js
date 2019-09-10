/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { activatePlugin, deactivatePlugin } from '../utils';

describe( 'AMP Settings Screen', () => {
	it( 'should display a welcome notice', async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toMatchElement( '.amp-welcome-notice h1', { text: 'Welcome to AMP for WordPress' } );
	} );

	it( 'should display a warning about missing object cache', async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toMatchElement( '.notice-warning p', { text: 'The AMP plugin performs at its best when persistent object cache is enabled' } );
	} );

	it( 'should display a message about theme compatibility', async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toMatchElement( '.notice-success p', { text: 'Your active theme is known to work well in standard or transitional mode.' } );
	} );

	it( 'should toggle Website Mode section', async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await page.evaluate( () => {
			document.querySelector( 'tr.amp-website-mode' ).scrollIntoView();
		} );

		const websiteModeSection = await page.$( 'tr.amp-website-mode' );

		expect( await websiteModeSection.isIntersectingViewport() ).toBe( true );

		await page.click( '#website_experience' );

		expect( await websiteModeSection.isIntersectingViewport() ).toBe( false );
	} );

	it( 'requires at least one AMP experience to be selected', async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		expect( await page.$eval( '#amp-settings', ( el ) => el.matches( ':invalid' ) ) ).toBe( false );

		await page.click( '#website_experience' );

		expect( await page.$eval( '#amp-settings', ( el ) => el.matches( ':invalid' ) ) ).toBe( true );
	} );

	it( 'should not allow AMP Stories to be enabled when Gutenberg is not active', async () => {
		await deactivatePlugin( 'gutenberg' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );

		expect( await page.$eval( '#stories_experience', ( el ) => el.matches( ':disabled' ) ) ).toBe( true );

		await expect( page ).toMatchElement( '.notice-info p', { text: 'To use stories, you currently must have the latest version' } );

		await activatePlugin( 'gutenberg' );
	} );

	it( 'should allow AMP Stories to be enabled when Gutenberg is active', async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		expect( await page.$eval( '#stories_experience', ( el ) => el.matches( ':disabled' ) ) ).toBe( false );
	} );
} );
