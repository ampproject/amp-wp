/**
 * WordPress dependencies
 */
import { activateTheme, createURL, installTheme, setBrowserViewport, visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { setTemplateMode } from '../../utils/amp-settings-utils';
import { assignMenuToLocation, createTestMenu } from '../../utils/nav-menu-utils';
import { DEFAULT_BROWSER_VIEWPORT_SIZE, MOBILE_BROWSER_VIEWPORT_SIZE } from '../../config/bootstrap';

describe( 'Twenty Nineteen theme on AMP', () => {
	beforeAll( async () => {
		await installTheme( 'twentynineteen' );
		await activateTheme( 'twentynineteen' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );
		await setTemplateMode( 'standard' );
	} );

	afterAll( async () => {
		await activateTheme( 'twentytwenty' );
	} );

	describe( 'main navigation on mobile', () => {
		beforeAll( async () => {
			await createTestMenu();
			await assignMenuToLocation( 'menu-1' );
		} );

		beforeEach( async () => {
			await setBrowserViewport( MOBILE_BROWSER_VIEWPORT_SIZE );
			await page.goto( createURL( '/' ) );
			await page.waitForSelector( '#page' );
		} );

		afterAll( async () => {
			await setBrowserViewport( DEFAULT_BROWSER_VIEWPORT_SIZE );
		} );

		it( 'should have a togglable submenu', async () => {
			await expect( page ).toMatchElement( '.main-navigation' );

			const menuItemWithSubmenu = await page.$( '.main-navigation .menu-item-has-children' );

			expect( menuItemWithSubmenu ).not.toBeNull();

			await expect( menuItemWithSubmenu ).toMatchElement( '.submenu-expand' );
			await expect( menuItemWithSubmenu ).toMatchElement( '.sub-menu', { visible: false } );

			await expect( menuItemWithSubmenu ).toClick( '.submenu-expand' );
			await expect( menuItemWithSubmenu ).toMatchElement( '.sub-menu', { visible: true } );
		} );
	} );
} );
