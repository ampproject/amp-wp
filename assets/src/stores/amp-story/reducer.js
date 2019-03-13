/**
 * WordPress dependencies
 */
import { select, combineReducers } from '@wordpress/data';

const { getBlock, getBlockOrder, getAdjacentBlockClientId } = select( 'core/editor' );

/**
 * Reducer handling animation order changes.
 *
 * @param {Object} state  Current state.
 * @param {Object} action Dispatched action.
 *
 * @return {Object} Updated state.
 */
export function animationOrder( state = {}, action ) {
	const newAnimationOrder = { ...state };
	const { page, item, predecessor } = action;
	const pageAnimationOrder = newAnimationOrder[ page ] || [];

	const entryIndex = ( entry ) => pageAnimationOrder.findIndex( ( { id } ) => id === entry );

	switch ( action.type ) {
		case 'ADD_ANIMATION':
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

			const parent = -1 !== entryIndex( predecessor ) && ! hasCycle( item, predecessor ) ? predecessor : undefined;

			if ( entryIndex( item ) !== -1 ) {
				pageAnimationOrder[ entryIndex( item ) ].parent = parent;
			} else {
				pageAnimationOrder.push( { id: item, parent } );
			}

			return {
				...newAnimationOrder,
				[ page ]: pageAnimationOrder,
			};

		case 'REMOVE_ANIMATION':
			if ( entryIndex( item ) !== -1 ) {
				const itemPredecessor = pageAnimationOrder[ entryIndex( item ) ].parent;
				pageAnimationOrder[ entryIndex( item ) ].parent = undefined;

				for ( const successor in pageAnimationOrder.filter( ( { parent: p } ) => p === item ) ) {
					pageAnimationOrder[ successor ].parent = itemPredecessor.parent;
				}
			}

			return {
				...newAnimationOrder,
				[ page ]: pageAnimationOrder,
			};
	}

	return state;
}

/**
 * Reducer handling changes to the current page.
 *
 * @param {Object} state  Current state.
 * @param {Object} action Dispatched action.
 *
 * @return {Object} Updated state.
 */
export function currentPage( state = undefined, action ) {
	const { page } = action;

	switch ( action.type ) {
		case 'REMOVE_PAGE':
			if ( page === state ) {
				return getAdjacentBlockClientId( page, -1 ) || getAdjacentBlockClientId( page, 1 ) || ( getBlockOrder() ? [ 0 ] : getBlockOrder() ) || undefined;
			}

			return state;
		case 'SET_CURRENT_PAGE':
			return getBlock( page ) ? page : state;
	}

	return state;
}

/**
 * Reducer handling block order.
 *
 * @param {Object} state  Current state.
 * @param {Object} action Dispatched action.
 *
 * @return {Object} Updated state.
 */
export function blocks( state = {}, action ) {
	switch ( action.type ) {
		case 'START_REORDERING':
			return {
				...state,
				order: getBlockOrder(),
				isReordering: true,
			};

		case 'STOP_REORDERING':
			return {
				...state,
				isReordering: false,
			};

		case 'MOVE_PAGE':
			const { page, index } = action;

			const oldIndex = state.indexOf( page );
			const newBlockOrder = [ ...state.order ];
			newBlockOrder.splice( index, 0, ...newBlockOrder.splice( oldIndex, 1 ) );

			return {
				...state,
				order: newBlockOrder,
			};

		case 'RESET_ORDER':
			return {
				...state,
				order: getBlockOrder(),
				isReordering: false,
			};
	}

	return state;
}

export default combineReducers( { animationOrder, currentPage, blocks } );
