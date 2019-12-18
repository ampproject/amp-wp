/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'addElement', () => {
	it( 'should add an element to the current page and select it', () => {
		const { restore, addElement } = setupReducer();

		// Set an initial state with a current page and no elements.
		restore( {
			pages: [ { id: '111', elements: [] } ],
			current: '111',
		} );

		const result = addElement( { element: { id: '123' } } );

		expect( result.pages[ 0 ] ).toStrictEqual( {
			id: '111',
			elements: [ { id: '123' } ],
		} );
		expect( result.selection ).toStrictEqual( [ '123' ] );
	} );

	it( 'should add an element to the end of the list on the current page and replace selection', () => {
		const { restore, addElement } = setupReducer();

		// Set an initial state with a current page and one element.
		restore( {
			pages: [ { id: '111', elements: [ { id: '321' } ] } ],
			current: '111',
			selection: [ '321' ],
		} );

		const result = addElement( { element: { id: '123' } } );

		expect( result.pages[ 0 ] ).toStrictEqual( {
			id: '111',
			elements: [ { id: '321' }, { id: '123' } ],
		} );
		expect( result.selection ).toStrictEqual( [ '123' ] );
	} );
} );
