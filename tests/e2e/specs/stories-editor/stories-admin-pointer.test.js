/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'AMP Stories Admin Pointer', () => {
	it( 'should be displayed', async () => {
		await visitAdminPage( '/' );

		await expect( page ).toMatchElement( '.wp-amp-pointer', { text: 'You can now enable Stories' } );
	} );
} );
