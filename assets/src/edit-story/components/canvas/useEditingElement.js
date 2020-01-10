/**
 * WordPress dependencies
 */
import { useState, useCallback } from '@wordpress/element';

function useEditingElement() {
	const [ editingElement, setEditingElement ] = useState( null );
	const [ editingElementState, setEditingElementState ] = useState( {} );
	const [ nodesById, setNodesById ] = useState( {} );

	const clearEditing = useCallback(
		() => setEditingElement( null ),
		[],
	);

	const setEditingElementWithoutState = useCallback(
		( id ) => {
			setEditingElement( id );
			setEditingElementState( {} );
		},
		[],
	);

	const setEditingElementWithState = useCallback(
		( id, state ) => {
			setEditingElement( id );
			setEditingElementState( state );
		},
		[],
	);

	const setNodeForElement = useCallback(
		( id, ref ) => setNodesById( ( oldNodes ) => ( { ...oldNodes, [ id ]: ref } ) ),
		[ setNodesById ],
	);

	return {
		nodesById,
		editingElement,
		editingElementState,
		setEditingElementWithState,
		setEditingElementWithoutState,
		clearEditing,
		setNodeForElement,
	};
}

export default useEditingElement;
