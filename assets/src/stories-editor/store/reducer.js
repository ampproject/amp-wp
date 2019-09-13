/**
 * WordPress dependencies
 */
import { combineReducers } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { isValidAnimationPredecessor } from './selectors';
import { ANIMATION_STATUS } from './constants';

function addAnimation( state, action ) {
	const animationOrder = { ...state.animationOrder };
	const { page, item, predecessor } = action;
	const pageAnimationOrder = animationOrder[ page ] || [];

	const entryIndex = ( entry ) => pageAnimationOrder.findIndex( ( { id } ) => id === entry );

	const parent = isValidAnimationPredecessor( { animations: state }, page, item, predecessor ) ? predecessor : undefined;

	if ( entryIndex( item ) !== -1 ) {
		pageAnimationOrder[ entryIndex( item ) ].parent = parent;
	} else {
		pageAnimationOrder.push( { id: item, parent } );
	}

	return {
		...state,
		animationOrder: {
			...animationOrder,
			[ page ]: pageAnimationOrder,
		},
	};
}

function changeAnimationType( state, action ) {
	const animationOrder = { ...state.animationOrder };
	const { page, item, animationType } = action;
	const pageAnimationOrder = animationOrder[ page ] || [];

	const entryIndex = ( entry ) => pageAnimationOrder.findIndex( ( { id } ) => id === entry );

	if ( entryIndex( item ) !== -1 ) {
		pageAnimationOrder[ entryIndex( item ) ].animationType = animationType;

		// If animation was disabled, update all successors.
		if ( ! animationType ) {
			const itemPredecessor = pageAnimationOrder[ entryIndex( item ) ].parent;
			const itemSuccessors = pageAnimationOrder.filter( ( { parent: p } ) => p === item );

			for ( const successor in itemSuccessors ) {
				if ( entryIndex( successor ) !== -1 ) {
					pageAnimationOrder[ entryIndex( successor ) ].parent = itemPredecessor.parent;
				}
			}
		}
	} else {
		pageAnimationOrder.push( { id: item, animationType } );
	}

	return {
		...state,
		animationOrder: {
			...animationOrder,
			[ page ]: pageAnimationOrder,
		},
	};
}

function changeAnimationDuration( state, action ) {
	const animationOrder = { ...state.animationOrder };
	const { page, item, duration } = action;
	const pageAnimationOrder = animationOrder[ page ] || [];

	const entryIndex = ( entry ) => pageAnimationOrder.findIndex( ( { id } ) => id === entry );

	if ( entryIndex( item ) !== -1 ) {
		pageAnimationOrder[ entryIndex( item ) ].duration = duration;
	}

	return {
		...state,
		animationOrder: {
			...animationOrder,
			[ page ]: pageAnimationOrder,
		},
	};
}

function changeAnimationDelay( state, action ) {
	const animationOrder = { ...state.animationOrder };
	const { page, item, delay } = action;
	const pageAnimationOrder = animationOrder[ page ] || [];

	const entryIndex = ( entry ) => pageAnimationOrder.findIndex( ( { id } ) => id === entry );

	if ( entryIndex( item ) !== -1 ) {
		pageAnimationOrder[ entryIndex( item ) ].delay = delay;
	}

	return {
		...state,
		animationOrder: {
			...animationOrder,
			[ page ]: pageAnimationOrder,
		},
	};
}

function playAnimation( state, action ) {
	const animationOrder = { ...state.animationOrder };
	const { page, item } = action;
	const pageAnimationOrder = animationOrder[ page ] || [];

	const entryIndex = ( entry ) => pageAnimationOrder.findIndex( ( { id } ) => id === entry );

	if ( item ) {
		if ( entryIndex( item ) !== -1 ) {
			pageAnimationOrder[ entryIndex( item ) ].status = ANIMATION_STATUS.playing;
		}
	} else {
		pageAnimationOrder.forEach( ( entry, index ) => {
			pageAnimationOrder[ index ] = { ...entry, status: entry.parent === undefined ? ANIMATION_STATUS.playing : ANIMATION_STATUS.prepared };
		} );
	}

	return {
		...state,
		animationOrder: {
			...animationOrder,
			[ page ]: pageAnimationOrder,
		},
	};
}

function finishAnimation( state, action ) {
	const animationOrder = { ...state.animationOrder };
	const { page, item } = action;
	const pageAnimationOrder = animationOrder[ page ] || [];

	const entryIndex = ( entry ) => pageAnimationOrder.findIndex( ( { id } ) => id === entry );

	if ( entryIndex( item ) !== -1 ) {
		pageAnimationOrder[ entryIndex( item ) ].status = ANIMATION_STATUS.finished;
	}

	return {
		...state,
		animationOrder: {
			...animationOrder,
			[ page ]: pageAnimationOrder,
		},
	};
}

function stopAnimation( state, action ) {
	const animationOrder = { ...state.animationOrder };
	const { page, item } = action;
	const pageAnimationOrder = animationOrder[ page ] || [];

	const entryIndex = ( entry ) => pageAnimationOrder.findIndex( ( { id } ) => id === entry );

	if ( item ) {
		if ( entryIndex( item ) !== -1 ) {
			pageAnimationOrder[ entryIndex( item ) ].status = ANIMATION_STATUS.stopped;
		}
	} else {
		pageAnimationOrder.forEach( ( entry, index ) => {
			pageAnimationOrder[ index ] = { ...entry, status: ANIMATION_STATUS.stopped };
		} );
	}

	return {
		...state,
		animationOrder: {
			...animationOrder,
			[ page ]: pageAnimationOrder,
		},
	};
}

/**
 * Reducer handling animation state changes.
 *
 * For each page, its animated elements with their
 * data (ID, duration, delay, predecessor) are stored.
 *
 * @param {Object} state  Current state.
 * @param {Object} action Dispatched action.
 *
 * @return {Object} Updated state.
 */
export function animations( state = {}, action ) {
	switch ( action.type ) {
		case 'ADD_ANIMATION':
			return addAnimation( state, action );

		case 'CHANGE_ANIMATION_TYPE':
			return changeAnimationType( state, action );

		case 'CHANGE_ANIMATION_DURATION':
			return changeAnimationDuration( state, action );

		case 'CHANGE_ANIMATION_DELAY':
			return changeAnimationDelay( state, action );

		case 'PLAY_ANIMATION':
			return playAnimation( state, action );

		case 'FINISH_ANIMATION':
			return finishAnimation( state, action );

		case 'STOP_ANIMATION':
			return stopAnimation( state, action );

		default:
			return state;
	}
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
		case 'SET_CURRENT_PAGE':
			return page;

		default:
			return state;
	}
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
	const { order } = action;

	switch ( action.type ) {
		case 'START_REORDERING':
			return {
				...state,
				order,
				isReordering: true,
			};

		case 'STOP_REORDERING':
			return {
				...state,
				isReordering: false,
			};

		case 'MOVE_PAGE':
			const { page, index } = action;

			const oldIndex = state.order.indexOf( page );
			const newBlockOrder = [ ...state.order ];
			newBlockOrder.splice( index, 0, ...newBlockOrder.splice( oldIndex, 1 ) );

			return {
				...state,
				order: newBlockOrder,
			};

		case 'RESET_ORDER':
			return {
				...state,
				order,
				isReordering: false,
			};

		default:
			return state;
	}
}

export function copiedMarkup( state = {}, action ) {
	switch ( action.type ) {
		case 'SET_COPIED_MARKUP':
			const { markup } = action;
			if ( 'string' === typeof markup ) {
				return markup;
			}
			return state;

		case 'CLEAR_COPIED_MARKUP':
			if ( '' === state ) {
				return state;
			}

			return '';

		default:
			return state;
	}
}

export default combineReducers( { animations, currentPage, blocks, copiedMarkup } );
