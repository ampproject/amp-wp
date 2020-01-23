/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useRef, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import useEditingElement from './useEditingElement';
import useCanvasSelectionCopyPaste from './useCanvasSelectionCopyPaste';
import Context from './context';
import { PAGE_WIDTH, PAGE_HEIGHT } from '../../constants';

function CanvasProvider( { children } ) {
	const [ lastSelectionEvent, setLastSelectionEvent ] = useState( null );

	const [ pageSize, setPageSize ] = useState( {width: PAGE_WIDTH, height: PAGE_HEIGHT} );
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
		actions: { toggleElementInSelection, setSelectedElementsById },
	} = useStory();

	const handleSelectElement = useCallback( ( elId, evt ) => {
		if ( editingElement && editingElement !== elId ) {
			clearEditing();
		}

		if ( evt.metaKey ) {
			toggleElementInSelection( { elementId: elId } );
		} else {
			setSelectedElementsById( { elementIds: [ elId ] } );
		}
		evt.stopPropagation();

		if ( 'mousedown' === evt.type ) {
			evt.persist();
			setLastSelectionEvent( evt );
		}
	}, [ editingElement, clearEditing, toggleElementInSelection, setSelectedElementsById ] );

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
		setSelectedElementsById( { elementIds: newSelectedElementIds } );
	}, [ currentPage, setSelectedElementsById ] );

	// Reset editing mode when selection changes.
	useEffect( () => {
		if ( editingElement &&
        ( selectedElementIds.length !== 1 || selectedElementIds[ 0 ] !== editingElement ) ) {
			clearEditing();
		}
	}, [ editingElement, selectedElementIds, clearEditing ] );

	useCanvasSelectionCopyPaste( pageContainer );

	const transformHandlersRef = useRef( {} );

	const registerTransformHandler = useCallback( ( id, handler ) => {
		const handlerListMap = transformHandlersRef.current;
		const handlerList = ( handlerListMap[ id ] || ( handlerListMap[ id ] = [] ) );
		handlerList.push( handler );
		return () => {
			handlerList.splice( handlerList.indexOf( handler ), 1 );
		};
	}, [ ] );

	const pushTransform = useCallback( ( id, transform ) => {
		const handlerListMap = transformHandlersRef.current;
		const handlerList = handlerListMap[ id ];
		if ( handlerList ) {
			handlerList.forEach( ( handler ) => handler( transform ) );
		}
	}, [ ] );

	const dataToEditorX = useCallback(
		( x ) => x * pageSize.width / PAGE_WIDTH,
		[ pageSize.width ] );
	const dataToEditorY = useCallback(
		( y ) => y * pageSize.height / PAGE_HEIGHT,
		[ pageSize.height ] );
	const editorToDataX = useCallback(
		( x ) => x * PAGE_WIDTH / pageSize.width,
		[ pageSize.width ] );
	const editorToDataY = useCallback(
		( y ) => y * PAGE_HEIGHT / pageSize.height,
		[ pageSize.height ] );

	const state = {
		state: {
			pageContainer,
			nodesById,
			editingElement,
			editingElementState,
			isEditing: Boolean( editingElement ),
			lastSelectionEvent,
			pageSize,
		},
		actions: {
			setPageContainer,
			setNodeForElement,
			setEditingElement: setEditingElementWithoutState,
			setEditingElementWithState,
			clearEditing,
			handleSelectElement,
			selectIntersection,
			registerTransformHandler,
			pushTransform,
			setPageSize,
			dataToEditorX,
			dataToEditorY,
			editorToDataX,
			editorToDataY,
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
