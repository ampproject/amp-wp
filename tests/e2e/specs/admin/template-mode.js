/**
 * Internal dependencies
 */
import { completeWizard } from '../../utils/onboarding-wizard-utils';
import { cleanUpValidatedUrls } from '../../utils/amp-settings-utils';

describe( 'Template Mode selector on AMP Settings screen', () => {
	it( 'does not show recommendations if site scan results are stale', async () => {
		await cleanUpValidatedUrls();
		await completeWizard( { technical: true, mode: 'transitional' } );

		// Scan results are stale right after completing the Wizard for any other template mode than Standard.
		await page.waitForSelector( '#template-modes' );
		await expect( page ).toMatchElement( '#template-mode-transitional:checked' );

		// None of the template modes should have a recommendation notice element.
		await expect( page ).not.toMatchElement( '#template-mode-standard-container .template-mode-selection__label-extra .amp-notice' );
		await expect( page ).not.toMatchElement( '#template-mode-transitional-container .template-mode-selection__label-extra .amp-notice' );
		await expect( page ).not.toMatchElement( '#template-mode-reader-container .template-mode-selection__label-extra .amp-notice' );
	} );
} );
