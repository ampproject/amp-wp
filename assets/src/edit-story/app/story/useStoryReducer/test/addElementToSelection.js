/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'addElementToSelection', () => {
	it( 'should add element to selection (if not already there)', () => {
		const { restore, addElementToSelection } = setupReducer();

		// Set an initial state.
		const initialState = restore( {
			pages: [
				{ id: '111', elements: [ { id: 'e1' }, { id: 'e2' }, { id: 'e3' } ] },
			],
			current: '111',
			selection: [],
		} );

		expect( initialState.selection ).not.toContain( 'e1' );

		// Add element e1
		const firstResult = addElementToSelection( { elementId: 'e1' } );
		expect( firstResult.selection ).toContain( 'e1' );

		// Add element e1 again - nothing happens
		const secondResult = addElementToSelection( { elementId: 'e1' } );
		expect( secondResult ).toStrictEqual( firstResult );
	} );

	it( 'should ignore missing element id', () => {
		const { restore, addElementToSelection } = setupReducer();

		// Set an initial state.
		const initialState = restore( {
			pages: [
				{ id: '111', elements: [ { id: 'e1' }, { id: 'e2' }, { id: 'e3' } ] },
			],
			current: '111',
			selection: [ 'e1', 'e2' ],
		} );

		// Add no element
		const failedAttempt = addElementToSelection( { elementId: null } );
		expect( failedAttempt ).toStrictEqual( initialState );
	} );

	it( 'should not allow adding background element to non-empty selection', () => {
		const { restore, addElementToSelection } = setupReducer();

		// Set an initial state.
		const initialState = restore( {
			pages: [
				{ id: '111', backgroundElementId: 'e1', elements: [ { id: 'e1' }, { id: 'e2' }, { id: 'e3' } ] },
			],
			current: '111',
			selection: [ 'e2', 'e3' ],
		} );

		// Toggle no element
		const failedAttempt = addElementToSelection( { elementId: 'e1' } );
		expect( failedAttempt ).toStrictEqual( initialState );
	} );
} );
