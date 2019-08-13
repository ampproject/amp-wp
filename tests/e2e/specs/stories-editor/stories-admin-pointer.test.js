/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'AMP Stories Admin Pointer', () => {
	it( 'should be displayed', async () => {
		await visitAdminPage( '/' );
		const nodes = await page.$x(
			'//*[contains(@class,"wp-amp-pointer")]//p[contains(text(), "You can now enable Stories")]'
		);
		expect( nodes.length ).not.toStrictEqual( 0 );
	} );
} );
