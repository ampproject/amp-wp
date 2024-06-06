/**
 * External dependencies
 */
import {
	afterAll,
	beforeAll,
	beforeEach,
	describe,
	expect,
	it,
} from '@jest/globals';

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
	DEFAULT_BROWSER_VIEWPORT_SIZE,
	MOBILE_BROWSER_VIEWPORT_SIZE,
} from '../../config/bootstrap';

describe('Twenty Twenty-Two theme on AMP', () => {
	beforeAll(async () => {
		await installTheme('twentytwentytwo');
		await activateTheme('twentytwentytwo');

		await visitAdminPage('admin.php', 'page=amp-options');
		await setTemplateMode('standard');
	});

	afterAll(async () => {
		await activateTheme('twentytwenty');
	});

	describe('header navigation on mobile', () => {
		const pageHeaderSelector = 'header.wp-block-template-part';

		beforeEach(async () => {
			await setBrowserViewport(MOBILE_BROWSER_VIEWPORT_SIZE);
			await page.goto(createURL('/'));
			await page.waitForSelector('.wp-site-blocks');
		});

		afterAll(async () => {
			await setBrowserViewport(DEFAULT_BROWSER_VIEWPORT_SIZE);
		});

		it('should be initially hidden', async () => {
			const pageHeaderElement = await page.$(pageHeaderSelector);
			expect(pageHeaderElement).not.toBeNull();

			await expect(pageHeaderElement).toMatchElement(
				'.wp-block-navigation__responsive-container-open'
			);
			await expect(pageHeaderElement).toMatchElement(
				'.wp-block-navigation__responsive-container[aria-hidden=true]',
				{ visible: false }
			);
			await expect(pageHeaderElement).toMatchElement(
				'.wp-block-navigation__responsive-container-close',
				{ visible: false }
			);
		});

		it('should be togglable', async () => {
			await page.waitForSelector(pageHeaderSelector);

			const pageHeaderElement = await page.$(pageHeaderSelector);
			expect(pageHeaderElement).not.toBeNull();

			await expect(pageHeaderElement).toClick(
				'.wp-block-navigation__responsive-container-open'
			);
			await expect(pageHeaderElement).toMatchElement(
				'.wp-block-navigation__responsive-container[aria-hidden=false]'
			);
			await expect(pageHeaderElement).toMatchElement(
				'.wp-block-navigation__responsive-container-close'
			);

			await expect(pageHeaderElement).toClick(
				'.wp-block-navigation__responsive-container-close'
			);
			await expect(pageHeaderElement).toMatchElement(
				'.wp-block-navigation__responsive-container[aria-hidden=true]',
				{ visible: false }
			);
			await expect(pageHeaderElement).toMatchElement(
				'.wp-block-navigation__responsive-container-close',
				{ visible: false }
			);
		});
	});
});
