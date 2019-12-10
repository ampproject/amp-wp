/**
 * Internal dependencies
 */
import { isInsideRange } from './utils';

/**
 * Set current page to the given index.
 *
 * If index is outside bounds of available pages, nothing happens.
 *
 * If page is changed, selection is cleared
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {number} payload.pageIndex Page index to set as current page
 * @return {Object} New state
 */
function setCurrentPage( state, { pageIndex } ) {
	const isWithinBounds = isInsideRange( pageIndex, 0, state.pages.length - 1 );
	if ( ! isWithinBounds ) {
		return state;
	}

	return {
		...state,
		current: pageIndex,
		selection: [],
	};
}

export default setCurrentPage;
