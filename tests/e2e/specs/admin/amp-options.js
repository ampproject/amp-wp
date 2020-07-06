/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';
/**
 * Internal dependencies
 */
import { completeWizard, cleanUpWizard } from '../utils/onboarding-wizard-utils';

describe( 'AMP settings screen newly activated', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );
	} );

	it( 'should not display the old welcome notice', async () => {
		await expect( page ).not.toMatchElement( '.amp-welcome-notice h2', { text: 'Welcome to AMP for WordPress' } );
	} );

	it( 'should display a message about theme compatibility', async () => {
		await expect( page ).toMatchElement( '.notice-success p', { text: 'Your active theme is known to work well in standard or transitional mode.' } );
	} );

	it( 'has main page components', async () => {
		await expect( page ).toMatchElement( 'h1', { text: 'AMP Settings' } );

		await expect( page ).toMatchElement( 'h2', { text: 'Configure AMP' } );
	} );
} );

describe( 'AMP Settings Screen after wizard', () => {
	beforeAll( async () => {
		await completeWizard( { technical: true, mode: 'standard' } );
		await visitAdminPage( 'admin.php', 'page=amp-options' );
	} );

	afterAll( async () => {
		await cleanUpWizard();
	} );

	it( 'has main page components', async () => {
		await expect( page ).toMatchElement( 'h1', { text: 'AMP Settings' } );

		await expect( page ).toMatchElement( 'h2', { text: 'AMP Settings Configured' } );
	} );
} );
