/**
 * This file is based on the core's <Draggable> Component.
 **/

/**
 * External dependencies
 */
import { noop } from 'lodash';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { withSafeTimeout } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import {
	getPixelsFromPercentage,
	getPercentageFromPixels,
	getRelativeElementPosition,
	isCTABlock,
} from '../../helpers';
import {
	STORY_PAGE_INNER_WIDTH,
	STORY_PAGE_INNER_HEIGHT,
	STORY_PAGE_MARGIN,
} from '../../constants';

const PAGE_AND_MARGIN = STORY_PAGE_INNER_WIDTH + STORY_PAGE_MARGIN;

const { Image, navigator } = window;

const cloneWrapperClass = 'components-draggable__clone';

const isChromeUA = ( ) => /Chrome/i.test( navigator.userAgent );
const documentHasIframes = ( ) => [ ...document.getElementById( 'editor' ).querySelectorAll( 'iframe' ) ].length > 0;

class Draggable extends Component {
	constructor( ...args ) {
		super( ...args );

		this.isChromeAndHasIframes = false;
	}

	/**
	 * In the image block, ensure that the preview image itself isn't draggable.
	 *
	 * When the image is draggable, it engages the DropZone of the Image block,
	 * not the DropZone that allows dragging the block on the page.
	 * There looks to be an easier way to do this with CSS,
	 * but -moz-user-drag: none; didn't work on Firefox.
	 */
	componentDidMount() {
		document.querySelectorAll( '.block-editor-block-list__block[data-type="core/image"] img' ).forEach( ( image ) => {
			image.setAttribute( 'draggable', 'false' );
		} );
	}

	componentWillUnmount() {
		this.resetDragState();
	}

	/**
	 * Removes the element clone, resets cursor, and removes drag listener.
	 *
	 * @param {Object} event The non-custom DragEvent.
	 */
	onDragEnd = ( event ) => {
		const { onNeighborDrop, onNeighborHover, blockName, setTimeout, onDragEnd = noop } = this.props;
		if ( event ) {
			event.preventDefault();
		}

		// Make sure to reset hover
		onNeighborHover( 0, true );

		// Attempt drop on neighbor if offset
		if ( this.pageOffset !== 0 ) {
			// All this is about calculating the position of the (correct) element on the new page.
			const currentElementTop = parseInt( this.cloneWrapper.style.top );
			const currentElementLeft = parseInt( this.cloneWrapper.style.left );
			const newLeft = currentElementLeft - ( this.pageOffset * PAGE_AND_MARGIN );
			const blockIsCTA = isCTABlock( blockName );
			const baseHeight = blockIsCTA ? STORY_PAGE_INNER_HEIGHT / 5 : STORY_PAGE_INNER_HEIGHT;
			const x = getPercentageFromPixels( 'x', newLeft, STORY_PAGE_INNER_WIDTH );
			const y = getPercentageFromPixels( 'y', currentElementTop, baseHeight );
			const xAttribute = blockIsCTA ? 'btnPositionLeft' : 'positionLeft';
			const yAttribute = blockIsCTA ? 'btnPositionTop' : 'positionTop';
			const newAttributes = {
				[ xAttribute ]: x,
				[ yAttribute ]: y,
			};
			onNeighborDrop( this.pageOffset, newAttributes );
		}

		this.resetDragState();
		setTimeout( onDragEnd );
	}

	/**
	 * Updates positioning of element clone based on mouse movement during dragging.
	 *
	 * @param  {Object} event The non-custom DragEvent.
	 */
	onDragOver = ( event ) => {
		const { onNeighborHover, blockName } = this.props;
		const top = parseInt( this.cloneWrapper.style.top ) + event.clientY - this.cursorTop;

		// Don't allow the CTA button to go over its top limit.
		if ( isCTABlock( blockName ) ) {
			this.cloneWrapper.style.top = top >= 0 ? `${ top }px` : '0px';
		} else {
			this.cloneWrapper.style.top = `${ top }px`;
		}

		this.cloneWrapper.style.left =
			`${ parseInt( this.cloneWrapper.style.left ) + event.clientX - this.cursorLeft }px`;

		// Update cursor coordinates.
		this.cursorLeft = event.clientX;
		this.cursorTop = event.clientY;

		// Check if mouse (*not* element, but actual cursor) is over neighboring page to either side.
		const currentElementLeft = parseInt( this.cloneWrapper.style.left );
		const cursorLeftRelativeToPage = currentElementLeft + this.cursorLeftInsideElement;
		const isOffRight = cursorLeftRelativeToPage > PAGE_AND_MARGIN;
		const isOffLeft = cursorLeftRelativeToPage < -STORY_PAGE_MARGIN;
		this.pageOffset = 0;
		if ( isOffLeft || isOffRight ) {
			// Check how far off we are to that side - on large screens you can drag elements 2+ pages over to either side.
			this.pageOffset = ( isOffLeft ?
				-Math.ceil( ( -STORY_PAGE_MARGIN - cursorLeftRelativeToPage ) / PAGE_AND_MARGIN ) :
				Math.ceil( ( cursorLeftRelativeToPage - PAGE_AND_MARGIN ) / PAGE_AND_MARGIN )
			);
		}
		onNeighborHover( this.pageOffset );
	}

	onDrop = () => {
		// As per https://html.spec.whatwg.org/multipage/dnd.html#dndevents
		// the target node for the dragend is the source node that started the drag operation,
		// while drop event's target is the current target element.
		this.onDragEnd( null );
	}

	/**
	 * Clones the current element and spawns clone over original element.
	 * Adds dragover listener.
	 *
	 * @param {Object} event Custom DragEvent.
	 */
	onDragStart = ( event ) => {
		const { blockName, elementId, transferData, onDragStart = noop } = this.props;
		const blockIsCTA = isCTABlock( blockName );
		// In the CTA block only the inner element (the button) is draggable, not the whole block.
		const element = blockIsCTA ? document.getElementById( elementId ) : document.getElementById( elementId ).parentNode;

		if ( ! element ) {
			event.preventDefault();
			return;
		}

		const parentPage = element.closest( 'div[data-type="amp/amp-story-page"]' );

		if ( ! parentPage ) {
			event.preventDefault();
			return;
		}

		/*
		 * On dragging, the browser creates an image of the target, for example, the entire text block.
		 * But there's already a clone below that's rotated in case the block is rotated,
		 * and this can create a non-rotated duplicate of that.
		 * So override this with a transparent image.
		 */
		const dragImage = new Image();
		dragImage.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=';
		event.dataTransfer.setDragImage( dragImage, 0, 0 );

		event.dataTransfer.setData( 'text', JSON.stringify( transferData ) );

		// Prepare element clone and append to element wrapper.
		const elementWrapper = element.parentNode;

		this.cloneWrapper = document.createElement( 'div' );
		this.cloneWrapper.classList.add( cloneWrapperClass );

		this.cloneWrapper.style.width = `${ element.clientWidth }px`;
		this.cloneWrapper.style.height = `${ element.clientHeight }px`;

		const clone = element.cloneNode( true );
		this.cloneWrapper.style.transform = clone.style.transform;

		// 20% of the full value in case of CTA block.
		const baseHeight = blockIsCTA ? STORY_PAGE_INNER_HEIGHT / 5 : STORY_PAGE_INNER_HEIGHT;

		// Position clone over the original element.
		const top = getPixelsFromPercentage( 'y', parseInt( clone.style.top ), baseHeight );
		const left = getPixelsFromPercentage( 'x', parseInt( clone.style.left ), STORY_PAGE_INNER_WIDTH );
		this.cloneWrapper.style.top = `${ top }px`;
		this.cloneWrapper.style.left = `${ left }px`;

		// Get starting position information
		const absolutePositionOfPage = getRelativeElementPosition( elementWrapper, document.documentElement );
		const absoluteElementLeft = absolutePositionOfPage.left + left;
		this.cursorLeftInsideElement = event.clientX - absoluteElementLeft;

		clone.id = `clone-${ elementId }`;
		clone.style.top = 0;
		clone.style.left = 0;
		clone.style.transform = 'none';

		// Hack: Remove iFrames as it's causing the embeds drag clone to freeze
		[ ...clone.querySelectorAll( 'iframe' ) ].forEach( ( child ) => child.parentNode.removeChild( child ) );

		this.cloneWrapper.appendChild( clone );
		elementWrapper.appendChild( this.cloneWrapper );

		// Mark the current cursor coordinates.
		this.cursorLeft = event.clientX;
		this.cursorTop = event.clientY;
		// Update cursor to 'grabbing', document wide.
		document.body.classList.add( 'is-dragging-components-draggable' );
		document.addEventListener( 'dragover', this.onDragOver );

		// Fixes https://bugs.chromium.org/p/chromium/issues/detail?id=737691#c8
		// dragend event won't be dispatched in the chrome browser
		// when iframes are affected by the drag operation. So, in that case,
		// we use the drop event to wrap up the dragging operation.
		// This way the hack is contained to a specific use case and the external API
		// still relies mostly on the dragend event.
		if ( isChromeUA() && documentHasIframes() ) {
			this.isChromeAndHasIframes = true;
			document.addEventListener( 'drop', this.onDrop );
		}

		this.props.setTimeout( onDragStart );
	}

	/**
	 * Cleans up drag state when drag has completed, or component unmounts
	 * while dragging.
	 */
	resetDragState = () => {
		// Remove drag clone
		document.removeEventListener( 'dragover', this.onDragOver );
		if ( this.cloneWrapper && this.cloneWrapper.parentNode ) {
			this.cloneWrapper.parentNode.removeChild( this.cloneWrapper );
			this.cloneWrapper = null;
		}

		if ( this.isChromeAndHasIframes ) {
			this.isChromeAndHasIframes = false;
			document.removeEventListener( 'drop', this.onDrop );
		}

		// Reset cursor.
		document.body.classList.remove( 'is-dragging-components-draggable' );
	}

	render() {
		const { children } = this.props;

		return children( {
			onDraggableStart: this.onDragStart,
			onDraggableEnd: this.onDragEnd,
		} );
	}
}

Draggable.propTypes = {
	blockName: PropTypes.string,
	elementId: PropTypes.string.isRequired,
	transferData: PropTypes.object,
	onDragStart: PropTypes.func,
	onDragEnd: PropTypes.func,
	onNeighborDrop: PropTypes.func.isRequired,
	onNeighborHover: PropTypes.func.isRequired,
	setTimeout: PropTypes.func.isRequired,
	children: PropTypes.any.isRequired,
};

export default withSafeTimeout( Draggable );
