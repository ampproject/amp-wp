/**
 * WordPress dependencies
 */
import { activateTheme, createURL, setBrowserViewport, visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { setTemplateMode } from '../../utils/amp-settings-utils';
import { assignMenuToLocation } from '../../utils/assign-menu-to-location';
import { DEFAULT_BROWSER_VIEWPORT_SIZE } from '../../config/bootstrap';

describe( 'Twenty Twenty theme on AMP', () => {
	beforeAll( async () => {
		await activateTheme( 'twentytwenty' );

		await visitAdminPage( 'admin.php', 'page=amp-options' );
		await setTemplateMode( 'standard' );
	} );

	describe( 'main navigation on mobile', () => {
		beforeAll( async () => {
			await assignMenuToLocation( 'mobile' );
		} );

		beforeEach( async () => {
			await setBrowserViewport( 'small' );
			await page.goto( createURL( '/' ) );
			await page.waitForSelector( '#site-header' );
		} );

		afterAll( async () => {
			await setBrowserViewport( DEFAULT_BROWSER_VIEWPORT_SIZE );
		} );

		it( 'should be initially hidden', async () => {
			await expect( page ).toMatchElement( '.mobile-nav-toggle[aria-expanded=false]' );
			await expect( page ).toMatchElement( '.menu-modal', { visible: false } );
		} );

		it( 'should be toggled on a button click', async () => {
			await expect( page ).toClick( '.mobile-nav-toggle' );
			await expect( page ).toMatchElement( '.mobile-nav-toggle[aria-expanded=true]' );
			await expect( page ).toMatchElement( '.menu-modal', { visible: true } );

			await expect( page ).toClick( '.mobile-nav-toggle' );
			await expect( page ).toMatchElement( '.mobile-nav-toggle[aria-expanded=false]' );
			await expect( page ).toMatchElement( '.menu-modal', { visible: false } );
		} );

		it( 'should have a togglable submenu', async () => {
			await expect( page ).toClick( '.mobile-nav-toggle' );

			const menuItemWithSubmenu = await page.$( '.menu-modal .menu-item-has-children' );

			expect( menuItemWithSubmenu ).not.toBeNull();

			await expect( menuItemWithSubmenu ).toMatchElement( '.sub-menu-toggle[aria-expanded=false]' );
			await expect( menuItemWithSubmenu ).toMatchElement( '.sub-menu', { visible: false } );

			await expect( menuItemWithSubmenu ).toClick( '.sub-menu-toggle' );
			await expect( menuItemWithSubmenu ).toMatchElement( '.sub-menu-toggle[aria-expanded=true]' );
			await expect( menuItemWithSubmenu ).toMatchElement( '.sub-menu', { visible: true } );

			await expect( menuItemWithSubmenu ).toClick( '.sub-menu-toggle' );
			await expect( menuItemWithSubmenu ).toMatchElement( '.sub-menu-toggle[aria-expanded=false]' );
			await expect( menuItemWithSubmenu ).toMatchElement( '.sub-menu', { visible: false } );
		} );
	} );

	describe( 'search modal on desktop', () => {
		beforeEach( async () => {
			await setBrowserViewport( DEFAULT_BROWSER_VIEWPORT_SIZE );

			await page.goto( createURL( '/' ) );
			await page.waitForSelector( '#site-header' );
		} );

		it( 'should be toggled on a button click', async () => {
			await expect( page ).toMatchElement( '.desktop-search-toggle[aria-expanded=false]' );
			await expect( page ).toMatchElement( '.search-modal', { visible: false } );

			await expect( page ).toClick( '.desktop-search-toggle' );
			await expect( page ).toMatchElement( '.search-toggle[aria-expanded=true]' );
			await expect( page ).toMatchElement( '.search-modal', { visible: true } );

			await expect( page ).toMatchElement( '.search-modal .close-search-toggle[aria-expanded=true]' );
			await expect( page ).toClick( '.search-modal .close-search-toggle' );
			await expect( page ).toMatchElement( '.search-modal', { visible: false } );
		} );
	} );
} );
