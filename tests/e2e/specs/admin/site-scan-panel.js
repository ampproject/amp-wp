/**
 * WordPress dependencies
 */
import { activateTheme, visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	activatePlugin,
	deactivatePlugin,
	installLocalPlugin,
	saveSettings,
	setTemplateMode,
	uninstallPlugin,
} from '../../utils/amp-settings-utils';
import {
	completeWizard,
	scrollToElement,
} from '../../utils/onboarding-wizard-utils';
import { testSiteScanning } from '../../utils/site-scan-utils';

describe('AMP settings screen Site Scan panel', () => {
	const timeout = 30000;

	describe('for a user not having an AMP validation capability', () => {
		beforeAll(async () => {
			await activatePlugin('do-not-allow-amp-validate-capability');
			await visitAdminPage('admin.php', 'page=amp-options');
		});

		afterAll(async () => {
			await deactivatePlugin('do-not-allow-amp-validate-capability');
		});

		it('does not render at all', async () => {
			await expect(page).toMatchElement('h1', { text: 'AMP Settings' });
			await expect(page).toMatchElement('#template-modes');
			await expect(page).not.toMatchElement('#site-scan');
		});

		it('has no impact if settings are changed', async () => {
			await setTemplateMode('standard');
			await expect(page).toMatchElement('#template-modes');
		});
	});

	describe('for a user having an AMP validation capability', () => {
		beforeAll(async () => {
			await completeWizard({ technical: true, mode: 'transitional' });
		});

		async function triggerSiteRescan() {
			await expect(page).toMatchElement('#site-scan h2', {
				text: 'Site Scan',
			});

			const isPanelCollapsed = await page.$eval(
				'#site-scan .components-panel__body-toggle',
				(el) => el.ariaExpanded === 'false'
			);
			if (isPanelCollapsed) {
				await scrollToElement({
					selector: '#site-scan .components-panel__body-toggle',
					click: true,
				});
			}

			// Start the site scan.
			await Promise.all([
				scrollToElement({
					selector: '.settings-site-scan__footer button.is-primary',
					click: true,
				}),
				testSiteScanning({
					statusElementClassName: 'settings-site-scan__status',
					isAmpFirst: false,
				}),
			]);

			await expect(page).toMatchElement(
				'.settings-site-scan__footer .is-primary',
				{ text: 'Rescan Site', timeout }
			);
			await expect(page).toMatchElement(
				'.settings-site-scan__footer .is-link',
				{ text: 'Browse Site' }
			);
		}

		it('does not list issues if an AMP compatible theme is activated', async () => {
			await visitAdminPage('admin.php', 'page=amp-options');

			await triggerSiteRescan();

			await expect(page).toMatchElement(
				'.settings-site-scan .amp-notice--success',
				{ timeout }
			);

			await expect(page).not.toMatchElement('.site-scan-results--themes');
			await expect(page).not.toMatchElement(
				'.site-scan-results--plugins'
			);

			// Reload the page and confirm that the panel is collapsed.
			await page.reload();
			await expect(page).toMatchElement(
				'#site-scan .components-panel__body-toggle[aria-expanded="false"]'
			);

			// Switch template mode to check if the scan results are marked as stale and the panel is initially expanded.
			await setTemplateMode('standard');

			await expect(page).toMatchElement(
				'#site-scan .components-panel__body-toggle[aria-expanded="true"]',
				{ timeout }
			);
			await expect(page).toMatchElement(
				'.settings-site-scan .amp-notice--info',
				{ text: /^Stale results/ }
			);
		});

		it('lists Hestia theme as causing AMP incompatibility', async () => {
			await activateTheme('hestia');

			await visitAdminPage('admin.php', 'page=amp-options');

			await triggerSiteRescan();

			await expect(page).toMatchElement(
				'.site-scan-results--themes .site-scan-results__heading[data-badge-content="1"]',
				{ text: /^Themes/, timeout }
			);
			await expect(page).toMatchElement(
				'.site-scan-results--themes .site-scan-results__source-name',
				{ text: /Hestia/ }
			);

			await activateTheme('twentytwenty');
		});

		it('lists E2E Tests Demo Plugin as causing AMP incompatibility', async () => {
			await activateTheme('twentytwenty');
			await activatePlugin('e2e-tests-demo-plugin');

			await visitAdminPage('admin.php', 'page=amp-options');

			await triggerSiteRescan();

			await expect(page).toMatchElement(
				'.site-scan-results--plugins .site-scan-results__heading[data-badge-content="1"]',
				{ text: /^Plugins/, timeout }
			);
			await expect(page).toMatchElement(
				'.site-scan-results--plugins .site-scan-results__source-name',
				{ text: /E2E Tests Demo Plugin/ }
			);

			await expect(page).not.toMatchElement('.site-scan-results--themes');

			await deactivatePlugin('e2e-tests-demo-plugin');
		});

		it('lists Hestia theme and E2E Tests Demo Plugin for causing AMP incompatibilities', async () => {
			await activateTheme('hestia');
			await activatePlugin('e2e-tests-demo-plugin');

			await visitAdminPage('admin.php', 'page=amp-options');

			await triggerSiteRescan();

			await expect(page).toMatchElement('.site-scan-results--themes', {
				timeout,
			});
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

			await activateTheme('twentytwenty');
			await deactivatePlugin('e2e-tests-demo-plugin');
		});

		it('displays a notice if a plugin has been deactivated or removed', async () => {
			await activateTheme('twentytwenty');
			await activatePlugin('e2e-tests-demo-plugin');

			await visitAdminPage('admin.php', 'page=amp-options');

			await triggerSiteRescan();

			await expect(page).toMatchElement(
				'.site-scan-results--plugins .site-scan-results__source-name',
				{ text: /E2E Tests Demo Plugin/, timeout }
			);

			// Deactivate the plugin and test.
			await deactivatePlugin('e2e-tests-demo-plugin');

			await visitAdminPage('admin.php', 'page=amp-options');

			await expect(page).toMatchElement(
				'.site-scan-results--plugins .site-scan-results__source-name',
				{ text: /E2E Tests Demo Plugin/ }
			);
			await expect(page).toMatchElement(
				'.site-scan-results--plugins .site-scan-results__source-notice',
				{
					text: /This plugin has been deactivated since last site scan./,
				}
			);

			// Uninstall the plugin and test.
			await uninstallPlugin('e2e-tests-demo-plugin');

			await visitAdminPage('admin.php', 'page=amp-options');

			await expect(page).toMatchElement(
				'.site-scan-results--plugins .site-scan-results__source-slug',
				{ text: /e2e-tests-demo-plugin/ }
			);
			await expect(page).toMatchElement(
				'.site-scan-results--plugins .site-scan-results__source-notice',
				{
					text: /This plugin has been uninstalled or its metadata is unavailable./,
				}
			);

			// Clean up.
			await installLocalPlugin('e2e-tests-demo-plugin');
		});

		it('automatically triggers a scan if Plugin Suppression option has changed', async () => {
			await activatePlugin('e2e-tests-demo-plugin');

			await visitAdminPage('admin.php', 'page=amp-options');

			// Suppress the plugin.
			await scrollToElement({
				selector: '#plugin-suppression .components-panel__body-toggle',
				click: true,
			});
			await expect(page).toSelect(
				'#suppressed-plugins-table tbody tr:first-child .column-status select',
				'Suppressed'
			);
			await saveSettings();

			await scrollToElement({ selector: '#site-scan' });
			await testSiteScanning({
				statusElementClassName: 'settings-site-scan__status',
				isAmpFirst: false,
			});

			await deactivatePlugin('e2e-tests-demo-plugin');
		});
	});
});
