/**
 * This file is mainly copied from core, the only difference is switching to using internal Draggable.
 */

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useIsBlockAllowedOnPage, useMoveBlockToPage } from '../../helpers';
import Draggable from './draggable';

const BlockDraggable = ( { children, clientId, blockName, blockElementId, onDragStart, onDragEnd } ) => {
	const { rootClientId } = useSelect( ( select ) => select( 'core/block-editor' ).getBlockRootClientId( clientId ) );
	const { index } = useSelect( ( select ) => select( 'core/block-editor' ).getBlockIndex( clientId, rootClientId ) );

	const transferData = {
		type: 'block',
		srcIndex: index,
		srcRootClientId: rootClientId,
		srcClientId: clientId,
	};

	const { moveBlockToPage, getPageByOffset } = useMoveBlockToPage( clientId );

	const isBlockAllowedOnPage = useIsBlockAllowedOnPage();

	// This holds the currently highlighted element, if any
	const hoverElement = useRef( { pageId: null, element: null, classes: [] } );

	/**
	 * Clear highlighting of pages.
	 */
	const clearHighlight = () => {
		// Unhighlight old highlighted page.
		if ( hoverElement.current.element ) {
			hoverElement.current.element.classList.remove( ...hoverElement.current.classes );
		}

		// Reset current hover element
		hoverElement.current = { pageId: null, element: null, classes: [] };
	};

	/**
	 * On the page given by the offset, set classes indicating that an element drag on this page is in progress, which element is being dragged and whether a drop is allowed on this page.
	 *
	 * @param {number} offset  Integer specifying offset from current page - e.g. -2 on page 3 will return id of page 1. Page indices are zero-based.
	 */
	const setHighlightByOffset = ( offset ) => {
		// Get neighboring page.
		const pageId = getPageByOffset( offset );
		const hasHighlightChanged = pageId !== hoverElement.current.pageId;

		if ( ! hasHighlightChanged ) {
			return;
		}

		clearHighlight();

		// Highlight page and mark whether drop is allowed or not.
		if ( pageId ) {
			// Drop is always allowed on the initial page (offset=0)
			const isAllowed = offset === 0 || isBlockAllowedOnPage( blockName, pageId );
			const classes = [
				'amp-page-draggable-hover',
				`amp-page-draggable-hover-${ blockName.replace( /\W/g, '-' ) }`,
				isAllowed ? 'amp-page-draggable-hover-droppable' : 'amp-page-draggable-hover-undroppable',
			];

			const element = document.getElementById( `block-${ pageId }` );
			element.classList.add( ...classes );

			hoverElement.current = { element, pageId, classes };
		}
	};

	/**
	 * Drop this element on the page `offset` away from the current page (if possible). If dropped, update the position of the element on the new page.
	 *
	 * Currently this function removes the old element and creates a clone on the new page.
	 *
	 * @param {number} offset  Integer specifying offset from current page - e.g. -2 on page 3 will return id of page 1. Offset must be non-zero.
	 * @param {Object} newAttributes  Object with attributes to update on element on new page.
	 */
	const dropElementByOffset = ( offset, newAttributes ) => {
		const pageId = getPageByOffset( offset );
		if ( ! pageId ) {
			return;
		}

		if ( ! isBlockAllowedOnPage( blockName, pageId ) ) {
			return;
		}

		moveBlockToPage( pageId, newAttributes );
	};

	return (
		<Draggable
			blockName={ blockName }
			elementId={ blockElementId }
			transferData={ transferData }
			onDragStart={ onDragStart }
			onDragEnd={ onDragEnd }
			clientId={ clientId }
			clearHighlight={ clearHighlight }
			setHighlightByOffset={ setHighlightByOffset }
			dropElementByOffset={ dropElementByOffset }
		>
			{
				( { onDraggableStart, onDraggableEnd } ) => {
					return children( {
						onDraggableStart,
						onDraggableEnd,
					} );
				}
			}
		</Draggable>
	);
};

BlockDraggable.propTypes = {
	clientId: PropTypes.string.isRequired,
	blockElementId: PropTypes.string.isRequired,
	blockName: PropTypes.string.isRequired,
	children: PropTypes.func.isRequired,
	onDragStart: PropTypes.func,
	onDragEnd: PropTypes.func,
};

export default BlockDraggable;

