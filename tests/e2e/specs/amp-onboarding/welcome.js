
/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'Welcome', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-setup' );
		await page.waitForSelector( '.amp-setup-nav__prev-next' );
	} );

	it( 'should contain content', async () => {
		await expect( page ).toMatchElement( '.welcome' );
	} );
} );
