/**
 * WordPress dependencies
 */
import { select, dispatch, registerStore } from '@wordpress/data';

const { getBlock } = select( 'core/editor' );
const { updateBlockAttributes } = dispatch( 'core/editor' );

export const namespace = 'amp/story';

const DEFAULT_STATE = {
	/**
	 * Holds a list of animated blocks per page.
	 *
	 * For each block, its clientId and its predecessor's (parent) clientId are stored.
	 */
	animationOrder: {},
};

const actions = {
	addAnimation( page, item, predecessor ) {
		return {
			type: 'ADD_ANIMATION',
			page,
			item,
			predecessor,
		};
	},
	removeAnimation( page, item ) {
		return {
			type: 'REMOVE_ANIMATION',
			page,
			item,
		};
	},
	removePage( page ) {
		return {
			type: 'REMOVE_PAGE',
			page,
		};
	},
};

const reducer = ( state = DEFAULT_STATE, action ) => {
	const { animationOrder } = state;
	const { type, page, item, predecessor } = action;

	const pageAnimationOrder = animationOrder[ page ] || [];

	const entryIndex = ( entry ) => pageAnimationOrder.findIndex( ( { id } ) => id === entry );

	const hasCycle = ( a, b ) => {
		let parent = b;

		while ( parent !== undefined ) {
			if ( parent === a ) {
				return true;
			}

			const parentItem = pageAnimationOrder.findIndex( ( { id } ) => id === parent );
			parent = parentItem ? parentItem.parent : undefined;
		}

		return false;
	};

	switch ( type ) {
		case 'ADD_ANIMATION':
			const parent = -1 !== entryIndex( predecessor ) && ! hasCycle( item, predecessor ) ? predecessor : undefined;

			if ( entryIndex( item ) !== -1 ) {
				pageAnimationOrder[ entryIndex( item ) ].parent = parent;
			} else {
				pageAnimationOrder.push( { id: item, parent: parent } );
			}

			animationOrder[ page ] = pageAnimationOrder;

			const parentBlock = parent ? getBlock( parent ) : undefined;

			updateBlockAttributes( item, { ampAnimationAfter: parentBlock ? parentBlock.attributes.anchor : undefined } );

			return {
				...state,
				...animationOrder,
			};
		case 'REMOVE_ANIMATION':
			if ( entryIndex( item ) !== -1 ) {
				pageAnimationOrder.splice( pageAnimationOrder.findIndex( ( { id } ) => id === item ), 1 );
				pageAnimationOrder
					.filter( ( { parent: p } ) => p === item )
					.map( ( p ) => {
						p.parent = pageAnimationOrder[ entryIndex( item ) ].parent;
						return p;
					} );
			}

			updateBlockAttributes( item, { ampAnimationAfter: undefined } );

			animationOrder[ page ] = pageAnimationOrder;

			return {
				...state,
				...animationOrder,
			};
		case 'REMOVE_PAGE':
			if ( animationOrder[ page ] ) {
				animationOrder[ page ] = undefined;
			}

			return {
				...state,
				...animationOrder,
			};
	}

	return state;
};

const selectors = {
	getAnimationOrder( state ) {
		return state.animationOrder || {};
	},
	getAnimationPredecessor( state, page, item ) {
		const pageAnimationOrder = state.animationOrder[ page ] || [];
		const found = pageAnimationOrder.find( ( { id } ) => id === item );

		return found ? found.parent : undefined;
	},
};

export const store = registerStore( namespace, { reducer, selectors, actions } );
