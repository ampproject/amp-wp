/**
 * Internal dependencies
 */
import { isInsideRange, moveArrayElement } from './utils';

/**
 * Move page in page order from the given index to the given position.
 *
 * If either index is outside bounds, nothing happens.
 * If indexes are the same, nothing happens.
 *
 * Current page remains unchanged in that current page index will point to
 * the same page object after the page has been moved.
 *
 * Current selection is unchanged.
 *
 * TODO: Handle multi-page re-order when UX and priority is finalized.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {number} payload.pageIndex Index of page to move to a new position.
 * @param {number} payload.position Index of where page should be moved to.
 * @return {Object} New state
 */
function movePage( state, { pageIndex, position } ) {
	const isIndexWithinBounds = isInsideRange( pageIndex, 0, state.pages.length - 1 );
	const isTargetWithinBounds = isInsideRange( position, 0, state.pages.length - 1 );
	const isSimilar = pageIndex === position;
	if ( ! isIndexWithinBounds || ! isTargetWithinBounds || isSimilar ) {
		return state;
	}

	const newPages = moveArrayElement( state.pages, pageIndex, position );

	const oldCurrentPage = state.pages[ state.current ];
	const newCurrent = newPages.indexOf( oldCurrentPage );

	return {
		...state,
		pages: newPages,
		current: newCurrent,
	};
}

export default movePage;
