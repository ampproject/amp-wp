/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { scrollToElement } from '../../utils/onboarding-wizard-utils';

describe( 'AMP analytics options', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );
		await page.addStyleTag( { content: 'html {scroll-behavior: auto !important;}' } );
	} );

	it( 'allows adding and deleting entries', async () => {
		await expect( page ).toClick( '#analytics-options .components-panel__body-toggle' );
		await expect( page ).not.toMatchElement( '.amp-analytics-entry' );

		await scrollToElement( { selector: '#amp-analytics-add-entry' } );

		// Add entry.
		await expect( page ).toClick( '#amp-analytics-add-entry' );
		await expect( '.amp-analytics-entry' ).countToBe( 1 );
		await expect( page ).toFill( '#amp-analytics-entry-1 input', 'googleanalytics' );

		// Add second entry.
		await expect( page ).toClick( '#amp-analytics-add-entry' );
		await expect( '.amp-analytics-entry' ).countToBe( 2 );
		await expect( page ).toFill( '#amp-analytics-entry-2 input', 'googleanalytics-2' );

		// Add third entry.
		await expect( page ).toClick( '#amp-analytics-add-entry' );
		await expect( '.amp-analytics-entry' ).countToBe( 3 );
		await expect( page ).toFill( '#amp-analytics-entry-3 input', 'alexametrics' );

		await expect( page ).toMatchElement( '#analytics-textarea-control-3', {
			value: JSON.stringify(
				{
					vars: {
						atrk_acct: '<YOURACCOUNT>',
						domain: '<YOURDOMAIN>',
					},
				},
				null,
				'\t',
			),
		} );

		// Save.
		await expect( page ).toClick( '.amp-settings-nav button[type="submit"]' );

		await page.waitForSelector( '.amp .amp-save-success-notice.amp-notice' );

		// Delete entries.
		await expect( page ).toClick( '.amp-analytics__delete-button' );
		await expect( page ).toClick( '.amp-analytics__delete-button' );
		await expect( page ).toClick( '.amp-analytics__delete-button' );

		await expect( page ).not.toMatchElement( '.amp-analytics-entry' );

		// Save.
		await expect( page ).toClick( '.amp-settings-nav button[type="submit"]' );
		await expect( page ).toMatchElement( '.amp .amp-save-success-notice.amp-notice' );
	} );
} );
