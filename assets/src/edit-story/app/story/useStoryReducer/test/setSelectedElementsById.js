/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'setSelectedElementsById', () => {
	it( 'should update selection', () => {
		const { restore, setSelectedElementsById } = setupReducer();

		// Set an initial state.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: 'e1' }, { id: 'e2' }, { id: 'e3' } ] },
			],
			current: '111',
			selection: [ 'e2', 'e1' ],
		} );

		// Select element 1 and 3
		const result = setSelectedElementsById( { elementIds: [ 'e1', 'e3' ] } );

		expect( result.selection ).toStrictEqual( [ 'e1', 'e3' ] );
	} );

	it( 'should remove duplicates', () => {
		const { restore, setSelectedElementsById } = setupReducer();

		// Set an initial state.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: 'e1' }, { id: 'e2' }, { id: 'e3' } ] },
			],
			current: '111',
			selection: [],
		} );

		// Select element 1 and 3 (and 1 again for weird reasons)
		const result = setSelectedElementsById( { elementIds: [ 'e1', 'e3', 'e1' ] } );

		expect( result.selection ).toStrictEqual( [ 'e1', 'e3' ] );
	} );

	it( 'should not update selection if nothing has changed', () => {
		const { restore, setSelectedElementsById } = setupReducer();

		// Set an initial state.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: 'e1' }, { id: 'e2' }, { id: 'e3' } ] },
			],
			current: '111',
			selection: [ 'e1', 'e2' ],
		} );

		// Update to e2+e1, which is the same as e1+e2.
		const result = setSelectedElementsById( { elementIds: [ 'e2', 'e1' ] } );

		expect( result.selection ).toStrictEqual( [ 'e1', 'e2' ] );
	} );

	it( 'should ignore non-list arguments', () => {
		const { restore, setSelectedElementsById } = setupReducer();

		// Set an initial state.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: 'e1' }, { id: 'e2' }, { id: 'e3' } ] },
			],
			current: '111',
			selection: [ 'e1', 'e2' ],
		} );

		// Can't clear by setting to null (hint: use clearSelection)
		const result = setSelectedElementsById( { elementIds: null } );

		expect( result.selection ).toStrictEqual( [ 'e1', 'e2' ] );
	} );

	it( 'should remove background if included among other elements', () => {
		const { restore, setSelectedElementsById } = setupReducer();

		// Set an initial state.
		restore( {
			pages: [
				{ id: '111', backgroundElementId: 'e1', elements: [ { id: 'e1' }, { id: 'e2' }, { id: 'e3' } ] },
			],
			current: '111',
			selection: [],
		} );

		// Try setting all elements as selected
		const result = setSelectedElementsById( { elementIds: [ 'e2', 'e1', 'e3' ] } );

		expect( result.selection ).toStrictEqual( [ 'e2', 'e3' ] );
	} );
} );
