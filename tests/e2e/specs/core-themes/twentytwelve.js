/**
 * WordPress dependencies
 */
import { activateTheme, createURL, installTheme, setBrowserViewport, visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { setTemplateMode } from '../../utils/amp-settings-utils';
import { assignMenuToLocation } from '../../utils/assign-menu-to-location';
import { DEFAULT_BROWSER_VIEWPORT_SIZE, MOBILE_BROWSER_VIEWPORT_SIZE } from '../../config/bootstrap';

describe( 'Twenty Twelve theme on AMP', () => {
	beforeAll( async () => {
		await installTheme( 'twentytwelve' );
		await activateTheme( 'twentytwelve' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );
		await setTemplateMode( 'standard' );
	} );

	afterAll( async () => {
		await activateTheme( 'twentytwenty' );
	} );

	describe( 'main navigation on mobile', () => {
		beforeAll( async () => {
			await assignMenuToLocation( 'primary' );
		} );

		beforeEach( async () => {
			await setBrowserViewport( MOBILE_BROWSER_VIEWPORT_SIZE );
			await page.goto( createURL( '/' ) );
			await page.waitForSelector( '#page' );
		} );

		afterAll( async () => {
			await setBrowserViewport( DEFAULT_BROWSER_VIEWPORT_SIZE );
		} );

		it( 'should be initially hidden', async () => {
			await expect( page ).toMatchElement( '#site-navigation .menu-toggle[aria-expanded=false]' );
			await expect( page ).toMatchElement( '#site-navigation .nav-menu', { visible: false } );
		} );

		it( 'should be togglable', async () => {
			await expect( page ).toClick( '#site-navigation .menu-toggle' );
			await expect( page ).toMatchElement( '#site-navigation .menu-toggle[aria-expanded=true]' );
			await expect( page ).toMatchElement( '#site-navigation .nav-menu', { visible: true } );

			// Submenus are expanded by default on twentytwelve mobile.
			await expect( page ).toMatchElement( '#site-navigation .nav-menu .menu-item-has-children .sub-menu', { visible: true } );

			await expect( page ).toClick( '#site-navigation .menu-toggle' );
			await expect( page ).toMatchElement( '#site-navigation .menu-toggle[aria-expanded=false]' );
			await expect( page ).toMatchElement( '#site-navigation .nav-menu', { visible: false } );
		} );
	} );
} );
