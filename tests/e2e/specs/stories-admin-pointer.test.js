/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'AMP Stories Admin Pointer', () => {
	it( 'Should be displayed', async () => {
		await visitAdminPage( '/' );
		const nodes = await page.$x(
			'//*[contains(@class,"wp-amp-pointer")]//p[contains(text(), "You can now enable Stories")]'
		);
		expect( nodes.length ).not.toEqual( 0 );
	} );
} );
