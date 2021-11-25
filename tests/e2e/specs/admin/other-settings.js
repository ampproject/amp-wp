/**
 * Internal dependencies
 */
import { cleanUpSettings, scrollToElement, completeWizard } from '../../utils/onboarding-wizard-utils';
import { saveSettings } from '../../utils/amp-settings-utils';

const panelSelector = '#other-settings .components-panel__body-toggle';

describe( 'Other settings', () => {
	beforeEach( async () => {
		await cleanUpSettings();
		await completeWizard( { technical: true, mode: 'transitional' } );
		await scrollToElement( { selector: panelSelector, click: true } );
	} );

	afterAll( async () => {
		await cleanUpSettings();
	} );

	it.each( [
		[
			'mobile redirect',
			'.mobile-redirection .amp-setting-toggle',
		],
		[
			'dev tools',
			'.developer-tools .amp-setting-toggle',
		],
	] )( 'persists the %s setting value', async ( title, toggleSelector ) => {
		const fullSelector = `#other-settings ${ toggleSelector } input[type="checkbox"]`;

		// Confirm the setting is initially enabled.
		await expect( page ).toMatchElement( `${ fullSelector }:checked` );

		// Disable the setting, save and reload.
		await scrollToElement( { selector: fullSelector, click: true } );
		await expect( page ).toMatchElement( `${ fullSelector }:not(:checked)` );
		await saveSettings();
		await page.reload();

		// Confirm the setting value has been persisted.
		await scrollToElement( { selector: panelSelector, click: true } );
		await expect( page ).toMatchElement( `${ fullSelector }:not(:checked)` );
	} );
} );
