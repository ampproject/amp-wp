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
	getAnimatedBlocks,
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

					if ( ! item ) {
						getAnimationSuccessors( state, page, id ).forEach( ( successor ) => {
							if ( successor.status !== ANIMATION_STATUS.prepared ) {
								dispatch( playAnimation( page, successor.id ) );
							}
						} );
					}
				}
			);
		} );
	},

	STOP_ANIMATION( action, { getState } ) {
		const { getBlock } = select( 'core/block-editor' );

		const state = getState();
		const { page, item } = action;
		const entries = item ? [ getAnimationEntry( state, page, item ) ] : getAnimatedBlocksPerPage( state, page );

		entries.forEach( ( { id, animationType } ) => {
			const block = getBlock( id );
			resetAnimationProperties( block, animationType );
		} );
	},

	SET_CURRENT_PAGE( action, { getState, dispatch } ) {
		const state = getState();

		Object.keys( getAnimatedBlocks( state ) ).forEach( ( page ) => {
			dispatch( stopAnimation( page ) );
		} );
	},
};
