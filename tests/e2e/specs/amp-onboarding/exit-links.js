/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	goToOnboardingWizard,
	cleanUpSettings,
	moveToDoneScreen,
} from '../../utils/onboarding-wizard-utils';

describe('Onboarding wizard exit links', () => {
	it('if no previous page, returns to settings when clicking close', async () => {
		await goToOnboardingWizard();

		await Promise.all([
			page.waitForNavigation(),
			expect(page).toClick('a', { text: 'Close' }),
		]);

		await page.waitForSelector('.wp-admin');

		await expect(page).toMatchElement('h1', { text: 'AMP Settings' });
	});

	// eslint-disable-next-line jest/no-disabled-tests
	it.skip('returns to previous page when clicking close', async () => {
		await visitAdminPage('admin.php', 'page=amp-options');
		await page.waitForSelector('.wp-admin');

		await page.waitForSelector(
			'a[href*="admin.php?page=amp-onboarding-wizard"]'
		);

		await Promise.all([
			page.waitForNavigation(),
			expect(page).toClick(
				'a[href*="admin.php?page=amp-onboarding-wizard"]'
			),
		]);

		await page.waitForSelector('#amp-onboarding-wizard');

		await Promise.all([
			page.waitForNavigation(),
			expect(page).toClick('a', { text: 'Close' }),
		]);

		await page.waitForSelector('.wp-admin');

		await expect(page).toMatchElement('h1', { text: 'AMP Settings' });
	});

	it('goes to settings when clicking finish', async () => {
		await moveToDoneScreen({ mode: 'standard' });

		await Promise.all([
			page.waitForNavigation(),
			expect(page).toClick('a', { text: 'Finish' }),
		]);

		await page.waitForSelector('.wp-admin');

		await expect(page).toMatchElement('h1', { text: 'AMP Settings' });

		await cleanUpSettings();
	});
});
