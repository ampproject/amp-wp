/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import {
	getAnimationEntry,
	getAnimatedBlocksPerPage,
	getAnimationSuccessors,
} from './selectors';
import {
	playAnimation,
	stopAnimation,
} from './actions';
import { startAnimation, resetAnimationProperties, setAnimationTransformProperties } from '../helpers';
import { ANIMATION_STATUS } from './constants';

export default {
	PLAY_ANIMATION( action, { getState, dispatch } ) {
		const { getBlock } = select( 'core/block-editor' );

		const state = getState();
		const { page, item } = action;

		if ( item ) {
			const { id, animationType } = getAnimationEntry( state, page, item );
			const block = getBlock( id );

			setAnimationTransformProperties( block, animationType );
		} else {
			getAnimatedBlocksPerPage( state, page ).forEach( ( { id, animationType } ) => {
				const block = getBlock( id );
				setAnimationTransformProperties( block, animationType );
			} );
		}

		const entries = item ? [ getAnimationEntry( state, page, item ) ] : getAnimationSuccessors( state, page, undefined );

		entries.forEach( ( { id, animationType, duration, delay } ) => {
			const block = getBlock( id );

			startAnimation(
				block,
				animationType,
				duration ? parseInt( duration ) : 0,
				delay ? parseInt( delay ) : 0,
				() => {
					dispatch( stopAnimation( page, id ) );

					getAnimationSuccessors( state, page, id )
						.filter( ( { status } ) => status === ANIMATION_STATUS.prepared )
						.forEach( ( successor ) => {
							dispatch( playAnimation( page, successor.id ) );
						} );
				}
			);
		} );
	},

	STOP_ANIMATION( action, { getState, dispatch } ) {
		const { getBlock } = select( 'core/block-editor' );

		const state = getState();
		const { page, item } = action;

		if ( item ) {
			const { id, animationType } = getAnimationEntry( state, page, item );
			const block = getBlock( id );
			resetAnimationProperties( block, animationType );
		} else {
			getAnimatedBlocksPerPage( state, page ).forEach( ( { id } ) => {
				dispatch( stopAnimation( page, id ) );
			} );
		}
	},
};
