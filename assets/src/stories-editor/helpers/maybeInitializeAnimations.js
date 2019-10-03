/**
 * External dependencies
 */
import { isEqual } from 'lodash';

/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data';

const { getAnimatedBlocks } = select( 'amp/story' );
const {
	addAnimation,
	changeAnimationType,
	changeAnimationDuration,
	changeAnimationDelay,
} = dispatch( 'amp/story' );

const {
	getBlocksByClientId,
	getBlockRootClientId,
	getClientIdsWithDescendants,
} = select( 'core/block-editor' );

/**
 * Initialize animation making sure that the predecessor animation has been initialized at first.
 *
 * @param {Object} block Animated block.
 * @param {Object} page Parent page.
 * @param {Object} allBlocks All blocks.
 */
const initializeAnimation = ( block, page, allBlocks ) => {
	const { ampAnimationAfter } = block.attributes;
	let predecessor;
	if ( ampAnimationAfter ) {
		predecessor = allBlocks.find( ( b ) => b.attributes.anchor === ampAnimationAfter );
	}

	if ( predecessor ) {
		const animations = getAnimatedBlocks();
		const pageAnimationOrder = animations[ page ] || [];
		const predecessorEntry = pageAnimationOrder.find( ( { id } ) => id === predecessor.clientId );

		// We need to initialize the predecessor first.
		if ( ! predecessorEntry ) {
			initializeAnimation( predecessor, page, allBlocks );
		}
	}
	addAnimation( page, block.clientId, predecessor ? predecessor.clientId : undefined );
};

/**
 * Initializes the animations if it hasn't been done yet.
 */
const maybeInitializeAnimations = () => {
	const animations = getAnimatedBlocks();
	if ( isEqual( {}, animations ) ) {
		const allBlocks = getBlocksByClientId( getClientIdsWithDescendants() );
		for ( const block of allBlocks ) {
			const page = getBlockRootClientId( block.clientId );

			if ( page ) {
				const { ampAnimationType, ampAnimationDuration, ampAnimationDelay } = block.attributes;
				initializeAnimation( block, page, allBlocks );

				changeAnimationType( page, block.clientId, ampAnimationType );
				changeAnimationDuration( page, block.clientId, ampAnimationDuration ? parseInt( String( ampAnimationDuration ).replace( 'ms', '' ) ) : undefined );
				changeAnimationDelay( page, block.clientId, ampAnimationDelay ? parseInt( String( ampAnimationDelay ).replace( 'ms', '' ) ) : undefined );
			}
		}
	}
};

export default maybeInitializeAnimations;
