/**
 * Internal dependencies
 */
import { addAMPAttributes } from '../';

describe( 'addAMPAttributes', () => {
	it( 'does not affect the page block', () => {
		const result = addAMPAttributes( {}, 'amp/amp-story-page' );

		expect( result ).toStrictEqual( {} );
	} );

	it( 'does not affect the template block', () => {
		const result = addAMPAttributes( {}, 'core/template' );

		expect( result ).toStrictEqual( {} );
	} );

	it.todo( 'add tests for other blocks' );
} );
