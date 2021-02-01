/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { testPreviousButton, testNextButton } from '../../utils/onboarding-wizard-utils';

describe( 'welcome', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-onboarding-wizard' );
		await page.waitForSelector( '.amp-settings-nav__prev-next' );
	} );

	it( 'should contain content', async () => {
		await expect( page ).toMatchElement( '.welcome' );

		testPreviousButton( { exists: false } );
		testNextButton( { text: 'Next' } );
	} );
} );
