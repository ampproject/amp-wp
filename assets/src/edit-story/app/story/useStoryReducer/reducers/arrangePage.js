/**
 * Internal dependencies
 */
import { isInsideRange, moveArrayElement } from './utils';

/**
 * Move page in page order with the given id to the given position.
 *
 * If new position is outside bounds, nothing happens.
 * If page is already at the new position, nothing happens.
 *
 * Current page remains unchanged (but might have moved in the page order).
 *
 * Current selection is unchanged.
 *
 * TODO: Handle multi-page re-order when UX and priority is finalized.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {number} payload.pageId Id of page to move to a new position.
 * @param {number} payload.position Index of where page should be moved to.
 * @return {Object} New state
 */
function arrangePage( state, { pageId, position } ) {
	// Abort if there's less than two elements (nothing to rearrange)
	if ( state.pages.length < 2 ) {
		return state;
	}

	const pageIndex = state.pages.findIndex( ( { id } ) => id === pageId );
	const isTargetWithinBounds = isInsideRange( position, 0, state.pages.length - 1 );
	const isSimilar = pageIndex === position;
	if ( pageIndex === -1 || ! isTargetWithinBounds || isSimilar ) {
		return state;
	}

	const newPages = moveArrayElement( state.pages, pageIndex, position );

	return {
		...state,
		pages: newPages,
	};
}

export default arrangePage;
