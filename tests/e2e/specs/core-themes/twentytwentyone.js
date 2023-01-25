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

describe('Twenty Twenty-One theme on AMP', () => {
	beforeAll(async () => {
		await installTheme('twentytwentyone');
		await activateTheme('twentytwentyone');

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
				'#primary-mobile-menu[aria-expanded=false]'
			);
			await expect(page).toMatchElement('#primary-menu-list', {
				visible: false,
			});
		});

		it('should be togglable', async () => {
			await expect(page).toClick('#primary-mobile-menu');
			await expect(page).toMatchElement(
				'#primary-mobile-menu[aria-expanded=true]'
			);
			await expect(page).toMatchElement('#primary-menu-list', {
				visible: true,
			});

			// Submenus are expanded by default on twentytwentyone mobile.
			await expect(page).toMatchElement(
				'#primary-menu-list .menu-item-has-children .sub-menu',
				{ visible: true }
			);

			await expect(page).toClick('#primary-mobile-menu');
			await expect(page).toMatchElement(
				'#primary-mobile-menu[aria-expanded=false]'
			);
			await expect(page).toMatchElement('#primary-menu-list', {
				visible: false,
			});
		});
	});
});
