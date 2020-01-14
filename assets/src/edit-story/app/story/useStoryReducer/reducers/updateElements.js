/**
 * Internal dependencies
 */
import { ELEMENT_RESERVED_PROPERTIES } from '../types';
import { intersect, objectWithout } from './utils';

/**
 * Update elements by the given list of ids with the given properties.
 * If given list of ids is `null`, update all currently selected elements.
 *
 * Elements will be updated only on the current page.
 *
 * If an element id does not correspond do an element on the current page, id is ignored.
 *
 * If an empty list or a list of only unknown ids is given, state is unchanged.
 *
 * If given set of properties is empty, state is unchanged.
 *
 * Current selection and page is unchanged.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {Array.<string>} payload.elementIds List of elements to update
 * @param {Object} payload.properties Properties to set on all the given elements.
 * @return {Object} New state
 */
function updateElements( state, { elementIds, properties } ) {
	const idsToUpdate = elementIds === null ? state.selection : elementIds;

	if ( idsToUpdate.length === 0 ) {
		return state;
	}

	const pageIndex = state.pages.findIndex( ( { id } ) => id === state.current );

	const oldPage = state.pages[ pageIndex ];
	const pageElementIds = oldPage.elements.map( ( { id } ) => id );

	// Nothing to update?
	const hasAnythingToUpdate = intersect( pageElementIds, idsToUpdate ).length > 0;
	if ( ! hasAnythingToUpdate ) {
		return state;
	}

	const allowedProperties = objectWithout( properties, ELEMENT_RESERVED_PROPERTIES );

	const updatedElements = oldPage.elements.map(
		( element ) => (
			idsToUpdate.includes( element.id ) ?
				{ ...element, ...allowedProperties } :
				element
		) );

	const newPage = {
		...oldPage,
		elements: updatedElements,
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

export default updateElements;
