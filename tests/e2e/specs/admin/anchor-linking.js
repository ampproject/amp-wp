
/**
 * WordPress dependencies
 */
import { loginUser } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { visitAdminPageWithHash } from '../../utils/visit-admin-page-with-hash';

describe( 'AMP settings page anchor linking', () => {
	beforeEach( async () => {
		await loginUser();
	} );

	it( 'jumps to supported templates section', async () => {
		await visitAdminPageWithHash( 'admin.php', 'page=amp-options', 'supported-templates' );
		await page.waitForSelector( '#supported-templates' );
		await expect( page ).toMatchElement( '#supported-templates .amp-drawer__panel-body.is-opened' );
	} );

	it( 'has analytics link that links to an open analytics drawer', async () => {
		await page.evaluate( () => {
			document.querySelector( 'a[href$="#analytics-options"]' ).click();
		} );

		await page.waitForSelector( '#analytics-options' );
		await expect( page ).toMatchElement( '#analytics-options .amp-drawer__panel-body.is-opened' );
	} );
} );

