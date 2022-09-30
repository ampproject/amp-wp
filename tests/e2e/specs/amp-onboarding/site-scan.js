/**
 * WordPress dependencies
 */
import { activateTheme } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	goToOnboardingWizard,
	moveToSiteScanScreen,
	testNextButton,
	testPreviousButton,
} from '../../utils/onboarding-wizard-utils';
import { testSiteScanning } from '../../utils/site-scan-utils';
import {
	activatePlugin,
	deactivatePlugin,
} from '../../utils/amp-settings-utils';

describe('Onboarding Wizard Site Scan Step', () => {
	it('should start a site scan immediately', async () => {
		await moveToSiteScanScreen({ technical: true });

		await Promise.all([
			expect(page).toMatchElement('.amp-onboarding-wizard-panel h1', {
				text: 'Site Scan',
			}),
			expect(page).toMatchElement('.site-scan__heading', {
				text: 'Please wait a minute',
			}),
			testNextButton({ text: 'Next', disabled: true }),
			testPreviousButton({ text: 'Previous', disabled: true }),
			testSiteScanning({
				statusElementClassName: 'site-scan__status',
				isAmpFirst: true,
			}),
		]);

		await expect(page).toMatchElement('.site-scan__heading', {
			text: 'Scan complete',
			timeout: 30000,
		});
		await expect(page).toMatchElement('.site-scan__section p', {
			text: /Site scan found no issues/,
		});

		await testNextButton({ text: 'Next' });
		await testPreviousButton({ text: 'Previous' });
	});

	it('should list out plugin and theme issues after the scan', async () => {
		await activateTheme('hestia');
		await activatePlugin('e2e-tests-demo-plugin');

		await moveToSiteScanScreen({ technical: true });

		await testSiteScanning({
			statusElementClassName: 'site-scan__status',
			isAmpFirst: true,
		});

		await expect(page).toMatchElement('.site-scan__heading', {
			text: 'Scan complete',
			timeout: 30000,
		});
		await expect(page).toMatchElement('.site-scan__section p', {
			text: /Site scan found issues/,
		});

		await expect(page).toMatchElement('.site-scan-results--themes');
		await expect(page).toMatchElement('.site-scan-results--plugins');

		const totalIssuesCount = await page.$$eval(
			'.site-scan-results__source',
			(sources) => sources.length
		);
		expect(totalIssuesCount).toBe(2);

		await expect(page).toMatchElement(
			'.site-scan-results--themes .site-scan-results__source-name',
			{ text: /Hestia/ }
		);
		await expect(page).toMatchElement(
			'.site-scan-results--plugins .site-scan-results__source-name',
			{ text: /E2E Tests Demo Plugin/ }
		);

		await testNextButton({ text: 'Next' });
		await testPreviousButton({ text: 'Previous' });

		await deactivatePlugin('e2e-tests-demo-plugin');
		await activateTheme('twentytwenty');
	});

	it('should not be present if the user has no validate capability', async () => {
		await activatePlugin('do-not-allow-amp-validate-capability');
		await goToOnboardingWizard();

		await expect(page).not.toMatchElement('.amp-stepper__item-title', {
			text: 'Site Scan',
		});

		await deactivatePlugin('do-not-allow-amp-validate-capability');
	});
});
