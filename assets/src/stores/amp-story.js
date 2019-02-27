/**
 * WordPress dependencies
 */
import { registerStore } from '@wordpress/data';

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
};

const reducer = ( state = DEFAULT_STATE, action ) => {
	const { animationOrder } = state;
	const { type, page, item, predecessor } = action;

	const pageAnimationOrder = animationOrder[ page ] || [];
	const found = pageAnimationOrder.findIndex( ( { id } ) => id === item );

	switch ( type ) {
		// Todo: Find cycles.
		case 'ADD_ANIMATION':
			if ( found !== -1 ) {
				pageAnimationOrder[ found ].parent = predecessor;
			} else {
				pageAnimationOrder.push( { id: item, parent: predecessor } );
			}

			animationOrder[ page ] = pageAnimationOrder;

			return {
				...state,
				...animationOrder,
			};
		case 'REMOVE_ANIMATION':
			if ( found !== -1 ) {
				pageAnimationOrder.splice( pageAnimationOrder.findIndex( ( { id } ) => id === item ), 1 );
				pageAnimationOrder
					.filter( ( { parent } ) => parent === item )
					.map( ( parent ) => {
						parent.parent = pageAnimationOrder[ found ].parent;
						return parent;
					} );
			}

			animationOrder[ page ] = pageAnimationOrder;

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

export const store = registerStore(	namespace, { reducer, selectors, actions } );
