/**
 * Internal dependencies
 */
import { addAMPAttributes } from '../';

describe( 'addAMPAttributes', () => {
	it( 'does not affect the page block', () => {
		const result = addAMPAttributes( {}, 'amp/amp-story-page' );

		expect( result ).toEqual( {} );
	} );

	it( 'does not affect the template block', () => {
		const result = addAMPAttributes( {}, 'core/template' );

		expect( result ).toEqual( {} );
	} );

	it.todo( 'add tests for other blocks' );
} );
