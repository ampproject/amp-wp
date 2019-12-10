/**
 * Internal dependencies
 */
import { isInsideRange, getAbsolutePosition, moveArrayElement } from './utils';

/**
 * Move element in element order on the given page.
 *
 * If no page is given, move element on the current page.
 *
 * If no element is given, check if selection only has on element, and if so, use that.
 * If no element and selection is empty or has multiple elements, state is unchanged.
 *
 * If element does not exist on given page, state is unchanged.
 *
 * Element is given by id and must be moved to the given position.
 * The position can either be a number from 0 to the number of elements, or a string
 * equal to one of the constants: FRONT, BACK, FORWARD, BACKWARD.
 *
 * FRONT is the same as moving to the highest possible number. BACK is the same as moving to 0.
 *
 * FORWARD and BACKWARD is changing the elements relative position by plus or minus 1 respectively.
 *
 * If element is already at the desired position, state is unchanged.
 *
 * Selection and current page is unchanged.
 *
 * TODO: Handle multi-element re-order when UX and priority is finalized.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {number} payload.pageIndex Index of page on which element exists
 * @param {string} payload.elementId Id of element to move
 * @param {number} payload.position New position of element to move
 * @return {Object} New state
 */
function moveElement( state, { pageIndex, elementId, position } ) {
	const indexToMoveAt = pageIndex === null ? state.current : pageIndex;

	const isWithinBounds = isInsideRange( indexToMoveAt, 0, state.pages.length - 1 );
	if ( ! isWithinBounds || ! elementId ) {
		return state;
	}

	const page = state.pages[ indexToMoveAt ];
	const elementPosition = page.elements.find( ( { id } ) => id === elementId );

	if ( elementPosition === -1 ) {
		return state;
	}

	const maxPosition = page.elements.length - 1;
	const newPosition = getAbsolutePosition( elementPosition, maxPosition, position );
	if ( elementPosition === newPosition || ! isInsideRange( newPosition, 0, maxPosition ) ) {
		return state;
	}

	const newElements = moveArrayElement( page.elements, elementPosition, newPosition );

	const newPages = [
		...state.pages.slice( 0, indexToMoveAt ),
		...{
			...page,
			elements: newElements,
		},
		...state.pages.slice( indexToMoveAt + 1 ),
	];

	return {
		...state,
		pages: newPages,
	};
}

export default moveElement;
