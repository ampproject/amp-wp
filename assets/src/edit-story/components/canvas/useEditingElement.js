/**
 * WordPress dependencies
 */
import { useState, useCallback, useEffect } from '@wordpress/element';

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

	// if any element is edited, make sure any touch or click outside this element exits edit mode.
	useEffect( () => {
		if ( ! editingElement ) {
			return undefined;
		}

		// TODO: it would make a hell of a lot more sense that this node is set when entering edit mode.
		// However, with the current data flow, that's a bit too complex.
		const root = nodesById[ editingElement ];

		if ( ! root ) {
			return undefined;
		}

		const handleClick = ( evt ) => {
			if ( ! root.contains( evt.target ) ) {
				clearEditing();
			}
		};

		// as soon as something is clicked/touched, check if we should exit edit mode.
		const doc = root.ownerDocument || root.document;
		doc.addEventListener( 'click', handleClick, true );

		return () => {
			doc.removeEventListener( 'click', handleClick, true );
		};
	}, [ editingElement, clearEditing, nodesById ] );

	return {
		editingElement,
		editingElementState,
		setEditingElementWithState,
		setEditingElementWithoutState,
		clearEditing,
		setNodeForElement,
	};
}

export default useEditingElement;
