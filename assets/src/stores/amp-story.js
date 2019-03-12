/**
 * WordPress dependencies
 */
import { select, dispatch, registerStore } from '@wordpress/data';

const { getBlock, getBlockOrder, getAdjacentBlockClientId } = select( 'core/editor' );
const { updateBlockAttributes } = dispatch( 'core/editor' );

export const namespace = 'amp/story';

const DEFAULT_STATE = {
	/**
	 * Holds a list of animated blocks per page.
	 *
	 * For each block, its clientId and its predecessor's (parent) clientId are stored.
	 */
	animationOrder: {},
	currentPage: undefined,
	blockOrder: [],
	isReordering: false,
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
	setCurrentPage( page ) {
		return {
			type: 'SET_CURRENT_PAGE',
			page,
		};
	},
	startReordering() {
		return {
			type: 'START_REORDERING',
		};
	},
	saveOrder() {
		return {
			type: 'STOP_REORDERING',
		};
	},
	movePageToPosition( page, index ) {
		return {
			type: 'MOVE_PAGE',
			page,
			index,
		};
	},
	resetOrder() {
		return {
			type: 'RESET_ORDER',
		};
	},
};

const reducer = ( state = DEFAULT_STATE, action ) => {
	const { animationOrder, currentPage, blockOrder } = state;
	const { type, page, item, predecessor, index } = action;

	const pageAnimationOrder = animationOrder[ page ] || [];

	const entryIndex = ( entry ) => pageAnimationOrder.findIndex( ( { id } ) => id === entry );

	const hasCycle = ( a, b ) => {
		let parent = b;

		while ( parent !== undefined ) {
			if ( parent === a ) {
				return true;
			}

			const parentItem = pageAnimationOrder.find( ( { id } ) => id === parent );
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
				pageAnimationOrder.push( { id: item, parent } );
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

			let newCurrentPage = currentPage;

			if ( page === currentPage ) {
				newCurrentPage = getAdjacentBlockClientId( page, -1 ) || getAdjacentBlockClientId( page, 1 ) || ( getBlockOrder() ? [ 0 ] : getBlockOrder() ) || undefined;
			}

			return {
				...state,
				...animationOrder,
				currentPage: newCurrentPage,
			};
		case 'SET_CURRENT_PAGE':
			return {
				...state,
				currentPage: getBlock( page ) ? page : currentPage,
			};
		case 'START_REORDERING':
			return {
				...state,
				blockOrder: getBlockOrder(),
				isReordering: true,
			};
		case 'STOP_REORDERING':
			return {
				...state,
				isReordering: false,
			};
		case 'MOVE_PAGE':
			const oldIndex = blockOrder.indexOf( page );
			const newBlockOrder = [ ...blockOrder ];
			newBlockOrder.splice( index, 0, ...newBlockOrder.splice( oldIndex, 1 ) );

			return {
				...state,
				blockOrder: newBlockOrder,
			};
		case 'RESET_ORDER':
			return {
				...state,
				blockOrder: getBlockOrder(),
				isReordering: false,
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
	getCurrentPage( state ) {
		return state.currentPage;
	},
	getBlockOrder( state ) {
		return state.blockOrder;
	},
	getBlockIndex( state, page ) {
		return state.blockOrder.indexOf( page );
	},
	isReordering( state ) {
		return state.isReordering;
	},
};

export const store = registerStore( namespace, { reducer, selectors, actions } );
