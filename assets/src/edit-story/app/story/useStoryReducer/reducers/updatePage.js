/**
 * Internal dependencies
 */
import { isInsideRange } from './utils';

/**
 * Update page by index or current page if no index given.
 *
 * If index is outside bounds of available pages, nothing happens.
 *
 * Current page and selection is unchanged.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {number} payload.pageIndex Page index to update. If null, update current page.
 * @param {number} payload.properties Object with properties to set for given page.
 * @return {Object} New state
 */
function updatePage( state, { pageIndex, properties } ) {
	const indexToUpdate = pageIndex === null ? state.current : pageIndex;

	const isWithinBounds = isInsideRange( indexToUpdate, 0, state.pages.length - 1 );
	if ( ! isWithinBounds ) {
		return state;
	}

	return {
		...state,
		pages: [
			...state.pages.slice( 0, indexToUpdate ),
			{
				...state.pages[ indexToUpdate ],
				...properties,
			},
			...state.pages.slice( indexToUpdate + 1 ),
		],
	};
}

export default updatePage;
