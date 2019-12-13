/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'arrangePage', () => {
	it( 'should reorder a page to the specified position', () => {
		const { restore, arrangePage } = setupReducer();

		// Set an initial state with multiple pages.
		restore( {
			pages: [ { id: '111' }, { id: '222' }, { id: '333' }, { id: '444' } ],
		} );

		// Reorder page 111 to fit between 222 and 333 - position 2
		const result = arrangePage( { pageId: '111', position: 2 } );
		const pageIds = result.pages.map( ( { id } ) => id );
		expect( pageIds ).toStrictEqual( [ '222', '111', '333', '444' ] );
	} );

	it( 'should do nothing if illegal reordering', () => {
		const { restore, arrangePage } = setupReducer();

		// Set an initial state with multiple pages.
		const initialState = restore( {
			pages: [ { id: '111' }, { id: '222' }, { id: '333' }, { id: '444' } ],
		} );

		// Reorder page 555 - doesn't exist
		const firstFailedAttempt = arrangePage( { pageId: '555', position: 2 } );
		expect( firstFailedAttempt ).toStrictEqual( initialState );

		// Reorder page 333 to position 2 - it's already there
		const secondFailedAttempt = arrangePage( { pageId: '333', position: 2 } );
		expect( secondFailedAttempt ).toStrictEqual( initialState );

		// Reorder page 333 to position 20 - outside bounds
		const thirdFailedAttempt = arrangePage( { pageId: '333', position: 20 } );
		expect( thirdFailedAttempt ).toStrictEqual( initialState );
	} );
} );
