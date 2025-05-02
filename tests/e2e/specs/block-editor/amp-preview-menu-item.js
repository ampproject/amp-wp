/**
 * WordPress dependencies
 */
import { createNewPost, visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { cleanUpSettings } from '../../utils/onboarding-wizard-utils';
import {
	activatePlugin,
	deactivatePlugin,
} from '../../utils/amp-settings-utils';

const ampPreviewMenuItemSelector = `.amp-editor-post-preview`;

describe('AMP Preview Menu Item', () => {
	it('is rendered on a new post', async () => {
		await createNewPost();

		// Open the Preview dropdown.
		const [previewMenuDropdownButton] = await page.$x(
			'//button[contains(@class, "editor-preview-dropdown__toggle")]'
		);

		await previewMenuDropdownButton.click();

		await expect(page).toMatchElement(ampPreviewMenuItemSelector);
	});

	it('is rendered when Gutenberg is disabled', async () => {
		await deactivatePlugin('gutenberg');

		await createNewPost();

		// Open the Preview dropdown.
		const [previewMenuDropdownButton] = await page.$x(
			'//button[contains(@class, "editor-preview-dropdown__toggle")]'
		);

		await previewMenuDropdownButton.click();

		await expect(page).toMatchElement(ampPreviewMenuItemSelector);

		await activatePlugin('gutenberg');
	});

	it('is rendered when a post has content', async () => {
		await createNewPost({
			title: 'The Ballad of the Lost Preview Button',
			content:
				'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consectetur fugiat, impedit.',
		});

		// Open the Preview dropdown.
		const [previewMenuDropdownButton] = await page.$x(
			'//button[contains(@class, "editor-preview-dropdown__toggle")]'
		);

		await previewMenuDropdownButton.click();

		await expect(page).toMatchElement(ampPreviewMenuItemSelector);
	});

	it('does not render the button when in Standard mode', async () => {
		// Set theme support to Standard mode.
		await visitAdminPage('admin.php', 'page=amp-options');
		await page.waitForSelector('.amp-settings-nav');
		await page.evaluate(async () => {
			await wp.apiFetch({
				path: '/amp/v1/options',
				method: 'POST',
				data: { theme_support: 'standard' },
			});
		});

		await createNewPost();

		// Open the Preview dropdown.
		const [previewMenuDropdownButton] = await page.$x(
			'//button[contains(@class, "editor-preview-dropdown__toggle")]'
		);

		await previewMenuDropdownButton.click();

		await expect(page).not.toMatchElement(ampPreviewMenuItemSelector);

		await cleanUpSettings();
	});
});
