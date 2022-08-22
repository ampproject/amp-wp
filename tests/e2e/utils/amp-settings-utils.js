/**
 * External dependencies
 */
import path from 'path';

/**
 * WordPress dependencies
 */
import {
	activatePlugin as _activatePlugin,
	deactivatePlugin as _deactivatePlugin,
	installPlugin as _installPlugin,
	switchUserToAdmin,
	switchUserToTest,
	uninstallPlugin as _uninstallPlugin,
	visitAdminPage,
} from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { scrollToElement } from './onboarding-wizard-utils';

export async function saveSettings() {
	await expect( page ).toClick( 'button', { text: 'Save' } );
	await expect( page ).toMatchElement( 'button[disabled]', { text: 'Save', timeout: 10000 } );
	await expect( page ).toMatchElement( '.amp-save-success-notice', { text: 'Saved' } );
}

export async function setTemplateMode( mode ) {
	await scrollToElement( { selector: `#template-mode-${ mode }`, click: true } );
	await saveSettings();
}

export async function isPluginInstalled( slug, settings ) {
	await switchUserToAdmin();
	await visitAdminPage( 'plugins.php', '' );
	await page.waitForSelector( 'h1', { text: 'Plugins' } );

	const found = await page.$( `tr${ settings?.checkIsActivated ? '.active' : '' }[data-slug="${ slug }"]` );

	await switchUserToTest();

	return Boolean( found );
}

export function isPluginActivated( slug ) {
	return isPluginInstalled( slug, { checkIsActivated: true } );
}

export async function installPlugin( slug ) {
	if ( ! await isPluginInstalled( slug ) ) {
		await _installPlugin( slug );
	}
}

/**
 * Install a plugin from a local directory.
 *
 * Note that the plugin ZIP archive should be located in the `/tests/e2e/plugins/` folder.
 * The filename should match the plugin slug provided as a parameter.
 *
 * @param {string} slug Plugin slug.
 */
export async function installLocalPlugin( slug ) {
	if ( await isPluginInstalled( slug ) ) {
		return;
	}

	await switchUserToAdmin();
	await visitAdminPage( 'plugin-install.php', '' );
	await page.waitForSelector( 'h1', { text: /Add Plugins/ } );

	await page.click( '.upload-view-toggle' );
	await page.waitForSelector( '#pluginzip' );

	const pluginPath = path.join( __dirname, '..', 'plugins', `${ slug }.zip` );
	await expect( page ).toUploadFile( '#pluginzip', pluginPath );

	await page.waitForSelector( '#install-plugin-submit:not([disabled])' );
	await page.click( '#install-plugin-submit' );
	await page.waitForSelector( 'p', { text: /Plugin installed successfully/ } );

	await switchUserToTest();
}

export async function activatePlugin( slug ) {
	await installPlugin( slug );

	if ( ! await isPluginActivated( slug ) ) {
		await _activatePlugin( slug );
	}
}

export async function deactivatePlugin( slug ) {
	if ( await isPluginActivated( slug ) ) {
		await _deactivatePlugin( slug );
	}
}

export async function uninstallPlugin( slug ) {
	await deactivatePlugin( slug );

	if ( await isPluginInstalled( slug ) ) {
		await _uninstallPlugin( slug );
	}
}

export async function cleanUpValidatedUrls() {
	await switchUserToAdmin();
	await visitAdminPage( 'edit.php', 'post_type=amp_validated_url' );
	await page.waitForSelector( 'h1' );

	const bulkSelector = await page.$( '#bulk-action-selector-top' );

	if ( ! bulkSelector ) {
		return;
	}

	await page.waitForSelector( '[id^=cb-select-all-]' );
	await page.click( '[id^=cb-select-all-]' );

	await page.select( '#bulk-action-selector-top', 'delete' );

	await page.click( '#doaction' );
	await page.waitForXPath( '//*[contains(@class, "updated notice")]/p[contains(text(), "forgotten")]' );
	await switchUserToTest();
}
