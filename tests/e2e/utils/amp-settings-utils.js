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

export async function setTemplateMode( mode ) {
	// Set template mode.
	const modeSelector = `#template-mode-${ mode }-container input`;

	await page.waitForSelector( modeSelector );
	await scrollToElement( { selector: modeSelector, click: true } );

	// Save options and wait for the request to succeed.
	const saveButtonSelector = '.amp-settings-nav button[type="submit"]';

	await page.waitForSelector( saveButtonSelector );

	await Promise.all( [
		scrollToElement( { selector: saveButtonSelector, click: true } ),
		page.waitForResponse( ( response ) => response.url().includes( '/wp-json/amp/v1/options' ), { timeout: 10000 } ),
	] );
}

export async function isPluginInstalled( slug, settings ) {
	await switchUserToAdmin();
	await visitAdminPage( 'plugins.php' );
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
