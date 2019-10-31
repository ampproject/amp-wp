/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { startAnimation, resetAnimationProperties, setAnimationTransformProperties } from '../helpers';
import {
	getAnimationEntry,
	getAnimatedBlocksPerPage,
	getAnimationSuccessors,
	getAnimatedBlocks,
} from './selectors';
import {
	playAnimation,
	stopAnimation,
	finishAnimation,
} from './actions';
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
				() => dispatch( finishAnimation( page, id ) ),
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

			if ( block && animationType ) {
				resetAnimationProperties( block, animationType );
			}
		} );
	},

	FINISH_ANIMATION( action, { getState, dispatch } ) {
		const state = getState();
		const { page, item } = action;

		const successors = getAnimationSuccessors( state, page, item )
			.filter( ( { status } ) => status && ( status === ANIMATION_STATUS.prepared ) );

		successors.forEach( ( successor ) => {
			dispatch( playAnimation( page, successor.id ) );
		} );

		const hasPlayingAnimations = getAnimatedBlocksPerPage( state, page ).find( ( { status } ) => {
			return status && ( status === ANIMATION_STATUS.prepared || status === ANIMATION_STATUS.playing );
		} );

		if ( ! hasPlayingAnimations ) {
			const entries = getAnimatedBlocksPerPage( state, page );

			entries.forEach( ( { id } ) => {
				dispatch( stopAnimation( page, id ) );
			} );
		}
	},

	SET_CURRENT_PAGE( action, { getState, dispatch } ) {
		const state = getState();

		Object.keys( getAnimatedBlocks( state ) ).forEach( ( page ) => {
			dispatch( stopAnimation( page ) );
		} );
	},
};
