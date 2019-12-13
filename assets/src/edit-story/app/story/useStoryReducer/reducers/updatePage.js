/**
 * Internal dependencies
 */
import { PAGE_RESERVED_PROPERTIES } from '../types';
import { objectWithout } from './utils';

/**
 * Update page by id or current page if no id given.
 *
 * If id doesn't exist, nothing happens.
 *
 * Current page and selection is unchanged.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {number} payload.pageId Page index to update. If null, update current page.
 * @param {number} payload.properties Object with properties to set for given page.
 * @return {Object} New state
 */
function updatePage( state, { pageId, properties } ) {
	const idToUpdate = pageId === null ? state.current : pageId;

	const pageIndex = state.pages.findIndex( ( { id } ) => id === idToUpdate );
	if ( pageIndex === -1 ) {
		return state;
	}

	const allowedProperties = objectWithout( properties, PAGE_RESERVED_PROPERTIES );

	const newPage = {
		...state.pages[ pageIndex ],
		...allowedProperties,
	};

	const newPages = [
		...state.pages.slice( 0, pageIndex ),
		newPage,
		...state.pages.slice( pageIndex + 1 ),
	];

	return {
		...state,
		pages: newPages,
	};
}

export default updatePage;
