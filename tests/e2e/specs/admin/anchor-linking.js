/**
 * WordPress dependencies
 */
import { loginUser } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { visitAdminPageWithHash } from '../../utils/visit-admin-page-with-hash';
import {
	cleanUpSettings,
	scrollToElement,
} from '../../utils/onboarding-wizard-utils';
import { saveSettings } from '../../utils/amp-settings-utils';

describe('AMP settings page anchor linking', () => {
	beforeEach(async () => {
		await loginUser();
	}, 400000);

	it('jumps to supported templates section', async () => {
		await visitAdminPageWithHash(
			'admin.php',
			'page=amp-options',
			'supported-templates'
		);
		await page.waitForSelector('#supported-templates');
		await expect(page).toMatchElement(
			'#supported-templates .amp-drawer__panel-body.is-opened'
		);
	});

	it('has analytics link that links to an open analytics drawer', async () => {
		await page.evaluate(() => {
			document.querySelector('a[href$="#analytics-options"]').click();
		});

		await page.waitForSelector('#analytics-options');
		await expect(page).toMatchElement(
			'#analytics-options .amp-drawer__panel-body.is-opened'
		);
	});
});

describe('AMP developer tools settings', () => {
	beforeEach(async () => {
		await loginUser();
		await visitAdminPageWithHash(
			'admin.php',
			'page=amp-options',
			'other-settings'
		);
	}, 400000);

	afterEach(async () => {
		await cleanUpSettings();
	});

	it('enables developer tools', async () => {
		const fullSelector = `#other-settings .developer-tools .amp-setting-toggle input[type="checkbox"]`;

		// Confirm the setting is initially enabled.
		await expect(page).toMatchElement(`${fullSelector}:checked`);

		const links = [
			'edit.php?post_type=amp_validated_url',
			'edit-tags.php?taxonomy=amp_validation_error&post_type=amp_validated_url',
		];

		await Promise.all(
			links.map((link) => {
				return expect(page).toMatchElement(`a[href$="${link}"]`);
			})
		);
	});

	it('disables developer tools', async () => {
		const fullSelector = `#other-settings .developer-tools .amp-setting-toggle input[type="checkbox"]`;

		// Confirm the setting is initially enabled.
		await expect(page).toMatchElement(`${fullSelector}:checked`);

		// Disable it
		await scrollToElement({ selector: fullSelector, click: true });
		await expect(page).toMatchElement(`${fullSelector}:not(:checked)`);

		const links = [
			'edit.php?post_type=amp_validated_url',
			'edit-tags.php?taxonomy=amp_validation_error&post_type=amp_validated_url',
		];

		await saveSettings();

		// Check after save.
		await Promise.all(
			links.map((link) => {
				return expect(page).not.toMatchElement(`a[href$="${link}"]`);
			})
		);
	});
});
