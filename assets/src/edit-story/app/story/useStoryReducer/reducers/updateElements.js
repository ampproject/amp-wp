/**
 * Internal dependencies
 */
import { intersect } from './utils';

/**
 * Update elements by the given list of ids with the given properties.
 * If given list of ids is `null`, update all currently selected elements.
 *
 * Elements will be updated regardless of which page they belong to. Even if
 * elements are located on different pages, all given elements will be updated.
 *
 * If an element id does not correspond do an element on any page, id is ignored.
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
	const hasAnyProperties = Object.keys( properties ).length > 0;

	if ( idsToUpdate.length === 0 || ! hasAnyProperties ) {
		return state;
	}

	const allElementIdsAcrossPages = state.pages.reduce(
		( list, { elements } ) => [
			...list,
			...elements.map( ( { id } ) => id ),
		],
		[],
	);

	// If no element on any page is to be updated, just return the state unchanged.
	if ( ! intersect( allElementIdsAcrossPages, idsToUpdate ) ) {
		return state;
	}

	const newPages = state.pages.map( ( page ) => {
		const pageElementIds = page.elements.map( ( { id } ) => id );

		// Don't touch pages unnecessarily
		if ( ! intersect( pageElementIds, idsToUpdate ) ) {
			return page;
		}

		const updatedElements = page.elements.map(
			( element ) => (
				idsToUpdate.includes( element.id ) ?
					{ ...element, ...properties } :
					element
			) );

		return {
			...page,
			elements: updatedElements,
		};
	} );

	return {
		...state,
		pages: newPages,
	};
}

export default updateElements;
