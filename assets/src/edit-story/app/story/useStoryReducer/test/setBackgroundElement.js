/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'setBackgroundElement', () => {
	it( 'should set the given background element and move it back', () => {
		const { restore, setBackgroundElement } = setupReducer();

		// Set an initial state with a current page and some elements.
		restore( {
			pages: [
				{
					id: '111',
					elements: [ { id: '123' }, { id: '456' }, { id: '789' } ],
					backgroundElementId: null,
				},
			],
			current: '111',
			selection: [],
		} );

		// 456 is to be bg - move it back and set it as background
		const result = setBackgroundElement( { elementId: '456' } );

		expect( result.pages[ 0 ] ).toStrictEqual( {
			id: '111',
			backgroundElementId: '456',
			elements: [ { id: '456' }, { id: '123' }, { id: '789' } ],
		} );
	} );

	it( 'should do nothing if already background', () => {
		const { restore, setBackgroundElement } = setupReducer();

		// Set an initial state with a current page and some elements.
		const initialState = restore( {
			pages: [
				{
					id: '111',
					elements: [ { id: '123' }, { id: '456' }, { id: '789' } ],
					backgroundElementId: '123',
				},
			],
			current: '111',
			selection: [],
		} );

		// 123 is already bg
		const result = setBackgroundElement( { elementId: '123' } );

		expect( result ).toStrictEqual( initialState );
	} );

	it( 'should do nothing if given unknown element', () => {
		const { restore, setBackgroundElement } = setupReducer();

		// Set an initial state with a current page and some elements.
		const initialState = restore( {
			pages: [
				{
					id: '111',
					elements: [ { id: '123' }, { id: '456' }, { id: '789' } ],
					backgroundElementId: '123',
				},
			],
			current: '111',
			selection: [],
		} );

		// 000 doesn't exist - nothing happens
		const result = setBackgroundElement( { elementId: '000' } );

		expect( result ).toStrictEqual( initialState );
	} );

	describe( 'when there is another background element', () => {
		it( 'should delete existing background element completely', () => {
			const { restore, setBackgroundElement } = setupReducer();

			// Set an initial state with a current page and no selection.
			restore( {
				pages: [
					{
						id: '111',
						elements: [ { id: '123' }, { id: '456' }, { id: '789' } ],
						backgroundElementId: '123',
					},
				],
				current: '111',
				selection: [],
			} );

			// 789 becomes background, 123 is deleted
			const result = setBackgroundElement( { elementId: '789' } );

			expect( result.pages[ 0 ] ).toStrictEqual( {
				id: '111',
				backgroundElementId: '789',
				elements: [ { id: '789' }, { id: '456' } ],
			} );
		} );

		it( 'should also delete existing background element from selection', () => {
			const { restore, setBackgroundElement } = setupReducer();

			// Set an initial state with a current page and all elements selected.
			restore( {
				pages: [
					{
						id: '111',
						elements: [ { id: '123' }, { id: '456' }, { id: '789' } ],
						backgroundElementId: '123',
					},
				],
				current: '111',
				selection: [ '123', '456', '789' ],
			} );

			// 789 becomes background, 123 is deleted (also from selection)
			const result = setBackgroundElement( { elementId: '789' } );

			expect( result.pages[ 0 ] ).toStrictEqual( {
				id: '111',
				backgroundElementId: '789',
				elements: [ { id: '789' }, { id: '456' } ],
			} );
			expect( result.selection ).toStrictEqual( [ '456', '789' ] );
		} );
	} );
} );
