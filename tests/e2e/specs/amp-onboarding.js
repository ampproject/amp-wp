/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'AMP Setup Screen', () => {
	it( 'should contain app root', async () => {
		await visitAdminPage( 'admin.php', 'page=amp-setup' );

		await expect( page ).toMatchElement( '#amp-setup' );
	} );
} );
