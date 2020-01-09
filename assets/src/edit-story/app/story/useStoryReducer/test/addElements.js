/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'addElements', () => {
	it( 'should ignore non-list arguments', () => {
		const { restore, addElements } = setupReducer();

		// Set an initial state with a current page and other elements.
		const initialState = restore( {
			pages: [ { id: '111', elements: [ { id: '000' } ] } ],
			current: '111',
		} );

		const result = addElements( { elements: false } );

		expect( result ).toStrictEqual( initialState );
	} );

	it( 'should ignore an empty list', () => {
		const { restore, addElements } = setupReducer();

		// Set an initial state with a current page and other elements.
		const initialState = restore( {
			pages: [ { id: '111', elements: [ { id: '000' } ] } ],
			current: '111',
		} );

		const result = addElements( { elements: [] } );

		expect( result ).toStrictEqual( initialState );
	} );

	it( 'should add all elements to the current page and select them', () => {
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

	it( 'should skip elements matching existing ids', () => {
		const { restore, addElements } = setupReducer();

		// Set an initial state with a current page and other elements.
		restore( {
			pages: [ { id: '111', elements: [ { id: '000', a: 1 } ] } ],
			current: '111',
		} );

		const result = addElements( { elements: [ { id: '123' }, { id: '000', a: 2 } ] } );

		expect( result.pages[ 0 ] ).toStrictEqual( {
			id: '111',
			elements: [ { id: '000', a: 1 }, { id: '123' } ],
		} );
		expect( result.selection ).toStrictEqual( [ '123' ] );
	} );

	it( 'should only add elements with unique ids (using the latter)', () => {
		const { restore, addElements } = setupReducer();

		// Set an initial state with a current page and other elements.
		restore( {
			pages: [ { id: '111', elements: [ { id: '000' } ] } ],
			current: '111',
		} );

		const result = addElements( { elements: [ { id: '123', a: 1 }, { id: '123', a: 2 } ] } );

		expect( result.pages[ 0 ] ).toStrictEqual( {
			id: '111',
			elements: [ { id: '000' }, { id: '123', a: 2 } ],
		} );
		expect( result.selection ).toStrictEqual( [ '123' ] );
	} );
} );
