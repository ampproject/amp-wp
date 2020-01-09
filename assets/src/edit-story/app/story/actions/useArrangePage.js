/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

/**
 * Move page in page order with the given id to the given position.
 *
 * If new position is outside bounds, nothing happens.
 * If page is already at the new position, nothing happens.
 *
 * Current page remains unchanged (but might have moved in the page order).
 *
 * Current selection is unchanged.
 *
 * TODO: This is a tmp function which will change with state management.
 */
function useArrangePage( { pages, setPages } ) {
	const arrangePage = useCallback( ( pageIndex, position ) => {
		// Abort if there's less than two elements (nothing to rearrange)
		if ( pages.length < 2 ) {
			return true;
		}

		const isInsideRange = ( index, start, end ) => {
			return index >= start && index <= end;
		};

		const moveArrayElement = ( array, oldPosition, newPosition ) => {
			// First remove from list.
			const element = array[ oldPosition ];
			const arrayWithoutElement = [
				...array.slice( 0, oldPosition ),
				...array.slice( oldPosition + 1 ),
			];

			// Then re-insert at the right point
			return [
				...arrayWithoutElement.slice( 0, newPosition ),
				element,
				...arrayWithoutElement.slice( newPosition ),
			];
		};

		const isTargetWithinBounds = isInsideRange( position, 0, pages.length - 1 );
		const isSimilar = pageIndex === position;
		if ( pageIndex === -1 || ! isTargetWithinBounds || isSimilar ) {
			return true;
		}

		const newPages = moveArrayElement( pages, pageIndex, position );

		setPages( newPages );
		return true;
	}, [ pages, setPages ] );
	return arrangePage;
}

export default useArrangePage;
