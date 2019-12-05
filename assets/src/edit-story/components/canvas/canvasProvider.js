/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useState, useCallback, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

function CanvasProvider( { children } ) {
	const [ editingElement, setEditingElement ] = useState( false );
	const [ editingElementState, setEditingElementState ] = useState( {} );
	const [ nodesById, setNodesById ] = useState( {} );

	// It's a bit weird to directly set a state to be a function (as setFoo calls
	// any function given to unwrap the inner value, which can then be a function),
	// so we use a wrapper object in stead of double-functioning.
	// We create helper functions below to set it directly.
	const [ backgroundClick, setBackgroundClick ] = useState( { handler: null } );

	const setBackgroundClickHandler = useCallback(
		( handler ) => setBackgroundClick( { handler: typeof handler === 'function' ? handler : null } ),
		[ setBackgroundClick ],
	);

	const clearBackgroundClickHandler = useCallback(
		() => setBackgroundClick( { handler: null } ),
		[ setBackgroundClick ],
	);

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

	const addNodeById = useCallback(
		( id, ref ) => setNodesById( ( oldNodes ) => ( { ...oldNodes, [ id ]: ref } ) ),
		[ setNodesById ],
	);

	// if any element is edited, make sure any touch or click outside this element exits edit mode.
	useEffect( () => {
		if ( ! editingElement ) {
			return undefined;
		}

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

	const state = {
		state: {
			editingElement,
			editingElementState,
			isEditing: Boolean( editingElement ),
			backgroundClickHandler: backgroundClick.handler,
		},
		actions: {
			addNodeById,
			setBackgroundClickHandler,
			clearBackgroundClickHandler,
			setEditingElement: setEditingElementWithoutState,
			setEditingElementWithState,
			clearEditing,
		},
	};

	return (
		<Context.Provider value={ state }>
			{ children }
		</Context.Provider>
	);
}

CanvasProvider.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default CanvasProvider;
