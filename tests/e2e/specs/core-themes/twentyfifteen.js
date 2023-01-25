/**
 * WordPress dependencies
 */
import {
	activateTheme,
	createURL,
	installTheme,
	setBrowserViewport,
	visitAdminPage,
} from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { setTemplateMode } from '../../utils/amp-settings-utils';
import {
	assignMenuToLocation,
	createTestMenu,
} from '../../utils/nav-menu-utils';
import {
	DEFAULT_BROWSER_VIEWPORT_SIZE,
	MOBILE_BROWSER_VIEWPORT_SIZE,
} from '../../config/bootstrap';

describe('Twenty Fifteen theme on AMP', () => {
	beforeAll(async () => {
		await installTheme('twentyfifteen');
		await activateTheme('twentyfifteen');

		await visitAdminPage('admin.php', 'page=amp-options');
		await setTemplateMode('standard');
	});

	afterAll(async () => {
		await activateTheme('twentytwenty');
	});

	describe('main navigation on mobile', () => {
		beforeAll(async () => {
			await createTestMenu();
			await assignMenuToLocation('primary');
		});

		beforeEach(async () => {
			await setBrowserViewport(MOBILE_BROWSER_VIEWPORT_SIZE);
			await page.goto(createURL('/'));
			await page.waitForSelector('#page');
		});

		afterAll(async () => {
			await setBrowserViewport(DEFAULT_BROWSER_VIEWPORT_SIZE);
		});

		it('should be initially hidden', async () => {
			await expect(page).toMatchElement(
				'.site-header .secondary-toggle[aria-expanded=false]'
			);
			await expect(page).toMatchElement('#site-navigation', {
				visible: false,
			});
		});

		it('should be togglable', async () => {
			await expect(page).toClick('.site-header .secondary-toggle');
			await expect(page).toMatchElement(
				'.site-header .secondary-toggle[aria-expanded=true]'
			);
			await expect(page).toMatchElement('#site-navigation', {
				visible: true,
			});

			await expect(page).toClick('.site-header .secondary-toggle');
			await expect(page).toMatchElement(
				'.site-header .secondary-toggle[aria-expanded=false]'
			);
			await expect(page).toMatchElement('#site-navigation', {
				visible: false,
			});
		});

		it('should have a togglable submenu', async () => {
			await expect(page).toClick('.site-header .secondary-toggle');

			const menuItemWithSubmenu = await page.$(
				'#site-navigation .menu-item-has-children'
			);

			expect(menuItemWithSubmenu).not.toBeNull();

			await expect(menuItemWithSubmenu).toMatchElement(
				'.dropdown-toggle[aria-expanded=false]'
			);
			await expect(menuItemWithSubmenu).toMatchElement('.sub-menu', {
				visible: false,
			});

			await expect(menuItemWithSubmenu).toClick('.dropdown-toggle');
			await expect(menuItemWithSubmenu).toMatchElement(
				'.dropdown-toggle[aria-expanded=true]'
			);
			await expect(menuItemWithSubmenu).toMatchElement('.sub-menu', {
				visible: true,
			});

			await expect(menuItemWithSubmenu).toClick('.dropdown-toggle');
			await expect(menuItemWithSubmenu).toMatchElement(
				'.dropdown-toggle[aria-expanded=false]'
			);
			await expect(menuItemWithSubmenu).toMatchElement('.sub-menu', {
				visible: false,
			});
		});
	});
});
