/**
 * Internal dependencies
 */
import reducer from '../reducer';

describe( 'reducer', () => {
	it( 'should do nothing if unknown action given', () => {
		const initialState = { pages: [] };

		const result = reducer( initialState, { type: 'UNKNOWN_ACTION', payload: {} } );

		expect( result ).toStrictEqual( initialState );
	} );
} );
