/**
 * Add elements to current page.
 *
 * Elements are expected to a be list of element objects with at least an id property.
 * No validation is made on these objects. If elements aren't a list, nothing happens.
 *
 * Elements will be added to the front (end) of the list of elements on the current page.
 *
 * Selection is set to be exactly the new elements.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {Array.<Object>} payload.elements Elements to insert on the given page.
 * @return {Object} New state
 */
function addElements( state, { elements } ) {
	const pageIndex = state.pages.finIndex( ( { id } ) => id === state.current );
	const oldPage = state.pages[ pageIndex ];
	const newPage = {
		...oldPage,
		elements: [
			...oldPage.elements,
			...elements,
		],
	};

	const newPages = [
		...state.pages.slice( 0, pageIndex ),
		newPage,
		...state.pages.slice( pageIndex + 1 ),
	];

	// Make list unique just in case some elements have the same id
	const newSelection = [ ...new Set( elements.map( ( { id } ) => id ) ) ];

	return {
		...state,
		pages: newPages,
		selection: newSelection,
	};
}

export default addElements;
