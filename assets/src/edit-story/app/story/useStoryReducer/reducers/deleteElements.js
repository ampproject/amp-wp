/**
 * Internal dependencies
 */
import { intersect } from './utils';

/**
 * Delete elements by the given list of ids.
 * If given list of ids is `null`, delete all currently selected elements.
 *
 * Elements will be deleted regardless of which page they belong to. Even if
 * elements are located on different pages, all given elements will be deleted.
 *
 * If an element id does not correspond do an element on any page, id is ignored.
 *
 * If an empty list or a list of only unknown ids is given, state is unchanged.
 *
 * If any id to delete is in current selection, deleted ids are removed from selection.
 * Otherwise selection is unchanged.
 *
 * Current page is unchanged.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {Array.<string>} payload.elementIds List of ids of elements to delete.
 * @return {Object} New state
 */
function deleteElements( state, { elementIds } ) {
	const idsToDelete = elementIds === null ? state.selection : elementIds;

	if ( idsToDelete.length === 0 ) {
		return state;
	}

	const allElementIdsAcrossPages = state.pages.reduce(
		( list, { elements } ) => [
			...list,
			...elements.map( ( { id } ) => id ),
		],
		[],
	);

	// If no element on any page is to be deleted, just return the state unchanged.
	if ( ! intersect( allElementIdsAcrossPages, idsToDelete ) ) {
		return state;
	}

	const newPages = state.pages.map( ( page ) => {
		const pageElementIds = page.elements.map( ( { id } ) => id );

		// Don't touch pages unnecessarily
		if ( ! intersect( pageElementIds, idsToDelete ) ) {
			return page;
		}

		const filteredElements = page.elements.filter( ( element ) => ! idsToDelete.includes( element.id ) );

		return {
			...page,
			elements: filteredElements,
		};
	} );

	// This check is to make sure not to modify the selection array if no update is necessary.
	const wasAnySelected = intersect( state.selection, idsToDelete );
	const newSelection = wasAnySelected ?
		state.selection.filter( ( id ) => ! idsToDelete.includes( id ) ) :
		state.selection;

	return {
		...state,
		pages: newPages,
		selection: newSelection,
	};
}

export default deleteElements;
