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
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {number} payload.elementId Element id to set as background on the current page.
 * @return {Object} New state
 */
function setBackgroundElement( state, { elementId } ) {
	const pageIndex = state.pages.find( ( { id } ) => id === state.current );

	const page = state.pages[ pageIndex ];

	let newPage;

	// If new id is null, clear background attribute and proceed
	if ( elementId === null ) {
		newPage = {
			...page,
			backgroundElementId: null,
		};
	} else {
		// Does the element even exist?
		const elementPosition = page.elements.find( ( { id } ) => id === elementId );
		if ( elementPosition === -1 ) {
			return state;
		}

		// Check if we already had a background id, if so, slice from after the first element.
		const sliceFrom = page.backgroundElementId !== null ? 1 : 0;

		// Reorder elements
		const newElements = moveArrayElement(
			page.elements.slice( sliceFrom ),
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
	};
}

export default setBackgroundElement;
