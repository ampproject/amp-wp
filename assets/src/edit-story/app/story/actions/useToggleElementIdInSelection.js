/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

function useToggleElementIdInSelection( {
	selectedElementIds,
	setSelectedElementIds,
} ) {
	const toggleElementIdInSelection = useCallback( ( id ) => {
		if ( selectedElementIds.includes( id ) ) {
			const index = selectedElementIds.indexOf( id );
			setSelectedElementIds( [
				...selectedElementIds.slice( 0, index ),
				...selectedElementIds.slice( index + 1 ),
			] );
		} else {
			setSelectedElementIds( [
				...selectedElementIds,
				id,
			] );
		}
	}, [ selectedElementIds, setSelectedElementIds ] );
	return toggleElementIdInSelection;
}

export default useToggleElementIdInSelection;
