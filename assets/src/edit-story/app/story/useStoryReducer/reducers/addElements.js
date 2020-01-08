/**
 * Add elements to current page.
 *
 * Elements are expected to a be list of element objects with at least an id property.
 * If any element id already exists on the page, element is skipped (not even updated).
 * If multiple elements in the new list have the same id, only the latter is used.
 *
 * If elements aren't a list or an empty list (after duplicates have been filtered), nothing happens.
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
	if ( ! Array.isArray( elements ) ) {
		return state;
	}

	const pageIndex = state.pages.findIndex( ( { id } ) => id === state.current );
	const oldPage = state.pages[ pageIndex ];
	const existingIds = oldPage.elements.map( ( { id } ) => id );
	const filteredElements = elements.filter( ( { id } ) => ! existingIds.includes( id ) );
	// Use only last of multiple elements with same id by turning into and object and getting the values.
	const deduplicatedElements = Object.values( Object.fromEntries(
		filteredElements.map( ( element ) => [ element.id, element ] ),
	) );

	if ( deduplicatedElements.length === 0 ) {
		return state;
	}

	const newPage = {
		...oldPage,
		elements: [
			...oldPage.elements,
			...deduplicatedElements,
		],
	};

	const newPages = [
		...state.pages.slice( 0, pageIndex ),
		newPage,
		...state.pages.slice( pageIndex + 1 ),
	];

	const newSelection = deduplicatedElements.map( ( { id } ) => id );

	return {
		...state,
		pages: newPages,
		selection: newSelection,
	};
}

export default addElements;
