/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'clearBackgroundElement', () => {
	it( 'should clear the background element', () => {
		const { restore, clearBackgroundElement } = setupReducer();

		// Set an initial state with a current page and some elements.
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

		const result = clearBackgroundElement();

		expect( result.pages[ 0 ] ).toStrictEqual( {
			id: '111',
			backgroundElementId: null,
			elements: [ { id: '123' }, { id: '456' }, { id: '789' } ],
		} );
	} );

	it( 'should do nothing if there is no background', () => {
		const { restore, clearBackgroundElement } = setupReducer();

		// Set an initial state with a current page and some elements.
		const initialState = restore( {
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

		const result = clearBackgroundElement();

		expect( result ).toStrictEqual( initialState );
	} );
} );
