/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

export const NEXT_BUTTON_SELECTOR = '#next-button';
export const PREV_BUTTON_SELECTOR =
	'.amp-settings-nav__prev-next button:not(.is-primary)';

export async function goToOnboardingWizard() {
	await visitAdminPage('index.php', '');
	await expect(page).not.toMatchElement('#amp-onboarding-wizard');
	await visitAdminPage('admin.php', 'page=amp-onboarding-wizard');
	await expect(page).toMatchElement('#amp-onboarding-wizard');
}

export async function clickNextButton() {
	await page.waitForSelector(`${NEXT_BUTTON_SELECTOR}:not([disabled])`);
	await expect(page).toClick(`${NEXT_BUTTON_SELECTOR}:not([disabled])`);
}

export async function clickPrevButton() {
	await expect(page).toClick(`${PREV_BUTTON_SELECTOR}:not([disabled])`);
}

export async function moveToTechnicalScreen() {
	await goToOnboardingWizard();
	await clickNextButton();
	await expect(page).toMatchElement('.technical-background-option');
}

export async function moveToSiteScanScreen({ technical }) {
	await moveToTechnicalScreen();

	const radioSelector = technical
		? '#technical-background-enable'
		: '#technical-background-disable';
	await expect(page).toClick(radioSelector);

	await clickNextButton();
	await expect(page).toMatchElement('.site-scan');
}

export async function moveToTemplateModeScreen({ technical }) {
	await moveToSiteScanScreen({ technical });

	await clickNextButton();
	await expect(page).toMatchElement('.template-mode-option');
}

export async function scrollToElement({ selector, click = false }) {
	await page.waitForSelector(selector);
	await page.evaluate(
		(options) => {
			const el = document.querySelector(options.selector);
			if (el) {
				el.scrollIntoView({ block: 'start', inline: 'center' });
				if (options.click) {
					el.click();
				}
			}
		},
		{ selector, click }
	);
}

export async function clickMode(mode) {
	await scrollToElement({ selector: `#template-mode-${mode}`, click: true });
}

export async function moveToReaderThemesScreen({ technical }) {
	await moveToTemplateModeScreen({ technical });
	await clickMode('reader');
	await clickNextButton();
	await page.waitForSelector('.theme-card');
}

export async function selectReaderTheme(theme = 'legacy') {
	const selector = `[for="theme-card__${theme}"]`;

	await page.waitForSelector(selector);
	await page.$eval(selector, (el) => el.click());
}

export async function moveToDoneScreen({
	technical = true,
	mode,
	readerTheme = 'legacy',
}) {
	if (mode === 'reader') {
		await moveToReaderThemesScreen({ technical });
		await selectReaderTheme(readerTheme);
	} else {
		await moveToTemplateModeScreen({ technical });
		await clickMode(mode);
	}

	await Promise.all([
		clickNextButton(),
		page.waitForResponse((response) =>
			response.url().includes('/wp-json/amp/v1/options')
		),
		page.waitForSelector('.done'),
	]);
}

export async function completeWizard({
	technical = true,
	mode,
	readerTheme = 'legacy',
}) {
	await moveToDoneScreen({ technical, mode, readerTheme });
	if ('reader' === mode) {
		await visitAdminPage('admin.php', 'page=amp-options');
	} else {
		await expect(page).toClick('#next-button');
	}
	await page.waitForSelector('#amp-settings', { timeout: 30000 });
	await expect(page).toMatchElement('#amp-settings');
}

export async function testCloseButton({ exists = true }) {
	if (exists) {
		await expect(page).toMatchElement('a', { text: 'Close' });
	} else {
		await expect(page).not.toMatchElement('a', { text: 'Close' });
	}
}

export async function testPreviousButton({
	exists = true,
	text = 'Previous',
	disabled = false,
}) {
	if (exists) {
		await expect(page).toMatchElement(
			`button${disabled ? '[disabled]' : ''}`,
			{ text }
		);
	} else {
		await expect(page).not.toMatchElement(
			`button${disabled ? '[disabled]' : ''}`,
			{ text }
		);
	}
}

export async function testNextButton({
	element = 'button',
	text = 'Next',
	disabled = false,
}) {
	await expect(page).toMatchElement(
		`${element}${disabled ? '[disabled]' : ''}`,
		{ text }
	);
}

export async function testTitle({ text, element = 'h1' }) {
	await expect(page).toMatchElement(element, { text });
}

/**
 * Reset plugin configuration.
 */
export async function cleanUpSettings() {
	await visitAdminPage('admin.php', 'page=amp-options');
	await page.waitForSelector('.amp-settings-nav');
	await page.evaluate(async () => {
		await Promise.all([
			wp.apiFetch({
				path: '/wp/v2/users/me',
				method: 'POST',
				data: {
					amp_dev_tools_enabled: true,
					amp_review_panel_dismissed_for_template_mode: '',
				},
			}),
			wp.apiFetch({
				path: '/amp/v1/options',
				method: 'POST',
				data: {
					mobile_redirect: false,
					reader_theme: 'legacy',
					theme_support: 'reader',
					plugin_configured: false,
				},
			}),
		]);
	});
}
