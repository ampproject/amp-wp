/**
 * Internal dependencies
 */
import effects from '../effects';
import * as selectors from '../selectors';
import { setCurrentPage, stopAnimation } from '../actions';

describe( 'effects', () => {
	describe( '.SET_CURRENT_PAGE', () => {
		const handler = effects.SET_CURRENT_PAGE;

		it( 'should stop all animations when changing the current page', () => {
			// eslint-disable-next-line import/namespace
			selectors.getAnimatedBlocks = () => {
				return {
					foo: [],
					bar: [],
				};
			};

			const dispatch = jest.fn();
			const getState = () => ( {} );
			handler( setCurrentPage( 'foo' ), { dispatch, getState } );

			expect( dispatch ).toHaveBeenCalledTimes( 2 );
			expect( dispatch ).toHaveBeenNthCalledWith( 1, stopAnimation( 'foo' ) );
			expect( dispatch ).toHaveBeenNthCalledWith( 2, stopAnimation( 'bar' ) );
		} );
	} );
} );
