/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'removeElementFromSelection', () => {
	it( 'should remove element from selection (if even there)', () => {
		const { restore, removeElementFromSelection } = setupReducer();

		// Set an initial state.
		const initialState = restore( {
			pages: [
				{ id: '111', elements: [ { id: 'e1' }, { id: 'e2' }, { id: 'e3' } ] },
			],
			current: '111',
			selection: [ 'e1', 'e2' ],
		} );

		expect( initialState.selection ).toContain( 'e1' );

		// Remove element e1
		const firstResult = removeElementFromSelection( { elementId: 'e1' } );
		expect( firstResult.selection ).not.toContain( 'e1' );

		// Remove element e1 again - nothing happens
		const secondResult = removeElementFromSelection( { elementId: 'e1' } );
		expect( secondResult ).toStrictEqual( firstResult );
	} );

	it( 'should ignore missing element id', () => {
		const { restore, removeElementFromSelection } = setupReducer();

		// Set an initial state.
		const initialState = restore( {
			pages: [
				{ id: '111', elements: [ { id: 'e1' }, { id: 'e2' }, { id: 'e3' } ] },
			],
			current: '111',
			selection: [ 'e1', 'e2' ],
		} );

		// Remove no element
		const failedAttempt = removeElementFromSelection( { elementId: null } );
		expect( failedAttempt ).toStrictEqual( initialState );
	} );
} );
