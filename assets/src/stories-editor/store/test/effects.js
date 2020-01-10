/**
 * Internal dependencies
 */
import effects from '../effects';
import * as selectors from '../selectors';
import { finishAnimation, setCurrentPage, stopAnimation } from '../actions';
import { ANIMATION_STATUS } from '../constants';

describe( 'effects', () => {
	describe( '.FINISH_ANIMATION', () => {
		const handler = effects.FINISH_ANIMATION;

		it( 'should stop all animations after they have finished', () => {
			// eslint-disable-next-line import/namespace
			selectors.getAnimatedBlocksPerPage = () => {
				return [
					{
						id: 'bar',
						status: ANIMATION_STATUS.finished,
					},
					{
						id: 'baz',
						status: ANIMATION_STATUS.stopped,
					},
				];
			};

			const dispatch = jest.fn();
			const getState = () => ( {} );
			handler( finishAnimation( 'foo', 'bar' ), { dispatch, getState } );

			expect( dispatch ).toHaveBeenCalledTimes( 2 );
			expect( dispatch ).toHaveBeenNthCalledWith( 1, stopAnimation( 'foo', 'bar' ) );
			expect( dispatch ).toHaveBeenNthCalledWith( 2, stopAnimation( 'foo', 'baz' ) );
		} );
	} );

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
