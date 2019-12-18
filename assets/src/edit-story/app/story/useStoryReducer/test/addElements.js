/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'addElements', () => {
	it( 'should add all element to the current page and select them', () => {
		const { restore, addElements } = setupReducer();

		// Set an initial state with a current page and other elements.
		restore( {
			pages: [ { id: '111', elements: [ { id: '000' } ] } ],
			current: '111',
		} );

		const result = addElements( { elements: [ { id: '123' }, { id: '234' } ] } );

		expect( result.pages[ 0 ] ).toStrictEqual( {
			id: '111',
			elements: [ { id: '000' }, { id: '123' }, { id: '234' } ],
		} );
		expect( result.selection ).toStrictEqual( [ '123', '234' ] );
	} );
} );
