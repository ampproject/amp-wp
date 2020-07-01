/**
 * Internal dependencies
 */
import { moveToSummaryScreen } from './utils';

describe( 'AMP wizard: summary', () => {
	it( 'renders standard mode summary', async () => {
		await moveToSummaryScreen( { mode: 'standard' } );

		const titleText = await page.$eval( 'h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Standard' );

		expect( page ).toMatchElement( '.desktop img' );
		expect( page ).not.toMatchElement( '.phone img' );
		expect( page ).not.toMatchElement( '.components-form-toggle' );
	} );

	it( 'renders transitional mode summary', async () => {
		await moveToSummaryScreen( { mode: 'transitional' } );

		const titleText = await page.$eval( 'h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Transitional' );

		expect( page ).toMatchElement( '.desktop img' );
		expect( page ).not.toMatchElement( '.phone img' );
		expect( page ).toMatchElement( '.components-form-toggle.is-checked' );
	} );

	it( 'renders reader mode summary', async () => {
		await moveToSummaryScreen( { mode: 'reader' } );

		const titleText = await page.$eval( 'h2', ( el ) => el.innerText );
		expect( titleText ).toBe( 'Reader' );

		expect( page ).toMatchElement( '.desktop img' );
		expect( page ).toMatchElement( '.phone img' );
		expect( page ).toMatchElement( '.components-form-toggle.is-checked' );
	} );
} );
