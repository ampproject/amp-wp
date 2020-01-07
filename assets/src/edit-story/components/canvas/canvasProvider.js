/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import useEditingElement from './useEditingElement';
import useCanvasSelectionCopyPaste from './useCanvasSelectionCopyPaste';
import Context from './context';

function CanvasProvider( { children } ) {
	const [ lastSelectionEvent, setLastSelectionEvent ] = useState( null );

	// @todo: most likely can be simplified/redone once we deal with changing
	// page size and offsets. We can simply pass the page's boundaries here
	// instead of the whole element.
	const [ pageContainer, setPageContainer ] = useState( null );

	const {
		nodesById,
		editingElement,
		editingElementState,
		setEditingElementWithState,
		setEditingElementWithoutState,
		clearEditing,
		setNodeForElement,
	} = useEditingElement();

	const {
		state: { currentPage, selectedElementIds },
		actions: { selectElementById, toggleElementIdInSelection, setSelectedElementIds },
	} = useStory();

	const handleSelectElement = useCallback( ( elId, evt ) => {
		if ( editingElement && editingElement !== elId ) {
			clearEditing();
		}

		if ( evt.metaKey ) {
			toggleElementIdInSelection( elId );
		} else {
			selectElementById( elId );
		}
		evt.stopPropagation();

		if ( 'mousedown' === evt.type ) {
			evt.persist();
			setLastSelectionEvent( evt );
		}
	}, [ editingElement, clearEditing, toggleElementIdInSelection, selectElementById ] );

	const selectIntersection = useCallback( ( { x: lx, y: ly, width: lw, height: lh } ) => {
		const newSelectedElementIds =
			currentPage.elements.filter( ( { x, y, width, height } ) => {
				return (
					x <= lx + lw &&
					lx <= x + width &&
					y <= ly + lh &&
					ly <= y + height
				);
			} ).map( ( { id } ) => id );
		setSelectedElementIds( newSelectedElementIds );
	}, [ currentPage, setSelectedElementIds ] );

	// Reset editing mode when selection changes.
	useEffect( () => {
		if ( editingElement &&
        ( selectedElementIds.length !== 1 || selectedElementIds[ 0 ] !== editingElement ) ) {
			clearEditing();
		}
	}, [ editingElement, selectedElementIds, clearEditing ] );

	useCanvasSelectionCopyPaste( pageContainer );

	const state = {
		state: {
			pageContainer,
			nodesById,
			editingElement,
			editingElementState,
			isEditing: Boolean( editingElement ),
			lastSelectionEvent,
		},
		actions: {
			setPageContainer,
			setNodeForElement,
			setEditingElement: setEditingElementWithoutState,
			setEditingElementWithState,
			clearEditing,
			handleSelectElement,
			selectIntersection,
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
