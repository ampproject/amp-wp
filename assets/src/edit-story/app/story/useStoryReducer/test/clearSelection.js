/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'clearSelection', () => {
	it( 'should clear selection if there is one', () => {
		const { restore, clearSelection } = setupReducer();

		// Set an initial state.
		restore( {
			pages: [
				{ id: '111', elements: [ { id: 'e1' }, { id: 'e2' }, { id: 'e3' } ] },
			],
			current: '111',
			selection: [ 'e2', 'e1' ],
		} );

		// Clear selection
		const result = clearSelection();

		expect( result.selection ).toStrictEqual( [ ] );
	} );
} );
