/**
 * Internal dependencies
 */
import { getAbsolutePosition, moveArrayElement } from './utils';

/**
 * Move element in element order on the current page.
 *
 * If no element is given, check if selection only has one element, and if so, use that.
 * If no element is given and selection is empty or has multiple elements, state is unchanged.
 *
 * If the element does not exist on the current page, state is unchanged.
 *
 * If the element is background element, state is unchanged (you can't reorder the background).
 *
 * Element is given by id and must be moved to the given position.
 * The position can either be a number from 0 to the number of elements, or a string
 * equal to one of the constants: FRONT, BACK, FORWARD, BACKWARD.
 *
 * FRONT is the same as moving to the highest possible number. BACK is the same as moving to 0.
 *
 * FORWARD and BACKWARD is changing the elements relative position by plus or minus 1 respectively.
 *
 * If there is a current background element, both BACK and position 0 is treated as position 1.
 *
 * If element is already at the desired position, state is unchanged.
 *
 * Selection and current page is unchanged.
 *
 * TODO: Handle multi-element re-order when UX and priority is finalized.
 *
 * @param {Object} state Current state
 * @param {Object} payload Action payload
 * @param {string} payload.elementId Id of element to move
 * @param {number} payload.position New position of element to move
 * @return {Object} New state
 */
function arrangeElement( state, { elementId, position } ) {
	const pageIndex = state.pages.findIndex( ( { id } ) => id === state.current );

	const page = state.pages[ pageIndex ];
	const currentPosition = page.elements.findIndex( ( { id } ) => id === elementId );

	if ( currentPosition === -1 || page.backgroundElementId === elementId ) {
		return state;
	}

	const minPosition = page.backgroundElementId !== null ? 1 : 0;
	const maxPosition = page.elements.length - 1;
	const newPosition = getAbsolutePosition( {
		currentPosition,
		minPosition,
		maxPosition,
		desiredPosition: position,
	} );

	// If it's already there, do nothing.
	if ( currentPosition === newPosition ) {
		return state;
	}

	const newElements = moveArrayElement( page.elements, currentPosition, newPosition );

	const newPages = [
		...state.pages.slice( 0, pageIndex ),
		...{
			...page,
			elements: newElements,
		},
		...state.pages.slice( pageIndex + 1 ),
	];

	return {
		...state,
		pages: newPages,
	};
}

export default arrangeElement;
