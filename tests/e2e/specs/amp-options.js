/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'AMP Settings Screen', () => {
	it( 'should display a welcome notice', async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toMatchElement( '.amp-welcome-notice h2', { text: 'Welcome to AMP for WordPress' } );
	} );

	it( 'should display a warning about missing object cache', async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toMatchElement( '.notice-warning p', { text: 'The AMP plugin performs at its best when persistent object cache is enabled' } );
	} );

	it( 'should display a message about theme compatibility', async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );

		await expect( page ).toMatchElement( '.notice-success p', { text: 'Your active theme is known to work well in standard or transitional mode.' } );
	} );

} );
