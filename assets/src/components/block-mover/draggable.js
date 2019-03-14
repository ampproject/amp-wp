/**
 * This file is based on the core's <Draggable> Component.
 **/

/**
 * External dependencies
 */
import { noop } from 'lodash';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { withSafeTimeout } from '@wordpress/compose';

const cloneWrapperClass = 'components-draggable__clone';

const isChromeUA = ( ) => /Chrome/i.test( window.navigator.userAgent );
const documentHasIframes = ( ) => [ ...document.getElementById( 'editor' ).querySelectorAll( 'iframe' ) ].length > 0;

class Draggable extends Component {
	constructor() {
		super( ...arguments );

		this.onDragStart = this.onDragStart.bind( this );
		this.onDragOver = this.onDragOver.bind( this );
		this.onDrop = this.onDrop.bind( this );
		this.onDragEnd = this.onDragEnd.bind( this );
		this.resetDragState = this.resetDragState.bind( this );

		this.isChromeAndHasIframes = false;
	}

	componentWillUnmount() {
		this.resetDragState();
	}

	/**
	 * Removes the element clone, resets cursor, and removes drag listener.
	 * @param  {Object} event     The non-custom DragEvent.
	 */
	onDragEnd( event ) {
		const { onDragEnd = noop } = this.props;
		if ( event ) {
			event.preventDefault();
		}

		this.resetDragState();
		this.props.setTimeout( onDragEnd );
	}

	/*
	 * Updates positioning of element clone based on mouse movement during dragging.
	 * @param  {Object} event     The non-custom DragEvent.
	 */
	onDragOver( event ) {
		this.cloneWrapper.style.top =
			`${ parseInt( this.cloneWrapper.style.top, 10 ) + event.clientY - this.cursorTop }px`;
		this.cloneWrapper.style.left =
			`${ parseInt( this.cloneWrapper.style.left, 10 ) + event.clientX - this.cursorLeft }px`;

		// Update cursor coordinates.
		this.cursorLeft = event.clientX;
		this.cursorTop = event.clientY;
	}

	onDrop( ) {
		// As per https://html.spec.whatwg.org/multipage/dnd.html#dndevents
		// the target node for the dragend is the source node that started the drag operation,
		// while drop event's target is the current target element.
		this.onDragEnd( null );
	}

	/**
	 *  - Clones the current element and spawns clone over original element.
	 *  - Sets transfer data.
	 *  - Adds dragover listener.
	 * @param  {Object} event					The non-custom DragEvent.
	 * @param  {string} elementId				The HTML id of the element to be dragged.
	 * @param  {Object} transferData			The data to be set to the event's dataTransfer - to be accessible in any later drop logic.
	 */
	onDragStart( event ) {
		const { elementId, transferData, onDragStart = noop } = this.props;
		const element = document.getElementById( elementId );
		const parentPage = element.closest( 'div[data-type="amp/amp-story-page"]' );
		if ( ! element || ! parentPage ) {
			event.preventDefault();
			return;
		}

		event.dataTransfer.setData( 'text', JSON.stringify( transferData ) );

		// Prepare element clone and append to element wrapper.
		const elementRect = element.getBoundingClientRect();
		const elementWrapper = element.parentNode;
		const elementTopOffset = parseInt( elementRect.top, 10 );
		const elementLeftOffset = parseInt( elementRect.left, 10 );

		const pageWrapperRect = parentPage.getBoundingClientRect();

		const clone = element.cloneNode( true );
		clone.id = `clone-${ elementId }`;
		clone.style.top = 0;
		clone.style.left = 0;
		this.cloneWrapper = document.createElement( 'div' );
		this.cloneWrapper.classList.add( cloneWrapperClass );
		this.cloneWrapper.style.width = `${ elementRect.width }px`;

		// Position clone over the original element.
		this.cloneWrapper.style.top = `${ elementTopOffset - parseInt( pageWrapperRect.top, 10 ) }px`;
		this.cloneWrapper.style.left = `${ elementLeftOffset - parseInt( pageWrapperRect.left, 10 ) }px`;

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
	resetDragState() {
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

export default withSafeTimeout( Draggable );
