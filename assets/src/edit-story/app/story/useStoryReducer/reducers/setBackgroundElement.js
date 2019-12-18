/**
 * Internal dependencies
 */
import { moveArrayElement } from './utils';

/**
 * Set background element on the current page to the given id.
 *
 * If element id is null, background id is cleared for the page.
 *
 * If page had a background element before, that element is deleted!
 * And if that element was selected, selection is cleared.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {number} payload.elementId Element id to set as background on the current page.
 * @return {Object} New state
 */
function setBackgroundElement( state, { elementId } ) {
	const pageIndex = state.pages.findIndex( ( { id } ) => id === state.current );

	const page = state.pages[ pageIndex ];

	let newPage;
	let newSelection = state.selection;

	// If new id is null, clear background attribute and proceed
	if ( elementId === null ) {
		if ( page.backgroundElementId === null ) {
			// Nothing to do here, there isn't any background to clear
			return state;
		}

		newPage = {
			...page,
			backgroundElementId: null,
		};
	} else {
		// Does the element even exist or is it already background
		let elementPosition = page.elements.findIndex( ( { id } ) => id === elementId );
		if ( elementPosition === -1 || page.backgroundElementId === elementId ) {
			return state;
		}
		let pageElements = page.elements;

		// Check if we already had a background id.
		const hadBackground = page.backgroundElementId !== null;
		if ( hadBackground ) {
			// If so, slice first element out and update position
			pageElements = pageElements.slice( 1 );
			elementPosition = elementPosition - 1;

			// Also remove old element from selection
			if ( state.selection.includes( page.backgroundElementId ) ) {
				newSelection = state.selection.filter( ( id ) => id !== page.backgroundElementId );
			}
		}

		// Reorder elements
		const newElements = moveArrayElement(
			pageElements,
			elementPosition,
			0,
		);

		newPage = {
			...page,
			backgroundElementId: elementId,
			elements: newElements,
		};
	}

	const newPages = [
		...state.pages.slice( 0, pageIndex ),
		newPage,
		...state.pages.slice( pageIndex + 1 ),
	];

	return {
		...state,
		pages: newPages,
		selection: newSelection,
	};
}

export default setBackgroundElement;
