/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'AMP Onboarding Screen', () => {
	it( 'should contain app root', async () => {
		await visitAdminPage( 'admin.php', 'page=amp-onboarding' );

		await expect( page ).toMatchElement( '#amp-onboarding' );
	} );
} );
