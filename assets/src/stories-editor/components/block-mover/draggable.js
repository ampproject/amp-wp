/**
 * This file is based on core's <Draggable> Component.
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
import { withSafeTimeout, compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import withSnapTargets from '../higher-order/with-snap-targets';
import {
	getPixelsFromPercentage,
	findClosestSnap, getRelativeElementPosition,
} from '../../helpers';
import {
	STORY_PAGE_INNER_WIDTH,
	STORY_PAGE_INNER_HEIGHT,
	BLOCK_DRAGGING_SNAP_GAP,
} from '../../constants';

const { Image, navigator } = window;

const cloneWrapperClass = 'components-draggable__clone';

const isChromeUA = ( ) => /Chrome/i.test( navigator.userAgent );
const documentHasIframes = ( ) => [ ...document.getElementById( 'editor' ).querySelectorAll( 'iframe' ) ].length > 0;

let lastX;
let lastY;

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
		const { onDragEnd = noop, snapLines, hideSnapLines, clearSnapLines } = this.props;
		if ( event ) {
			event.preventDefault();
		}

		this.resetDragState();

		hideSnapLines();
		if ( snapLines.length ) {
			clearSnapLines();
		}

		this.props.setTimeout( onDragEnd );
	}

	/**
	 * Updates positioning of element clone based on mouse movement during dragging.
	 *
	 * @param  {Object} event The non-custom DragEvent.
	 */
	onDragOver = ( event ) => { // eslint-disable-line complexity
		const {
			snapLines,
			clearSnapLines,
			setSnapLines,
			parentBlockElement,
		} = this.props;

		const top = parseInt( this.cloneWrapper.style.top ) + event.clientY - this.cursorTop;
		const left = parseInt( this.cloneWrapper.style.left ) + event.clientX - this.cursorLeft;

		if ( top === lastY && left === lastX ) {
			return;
		}

		// Get the correct dimensions in case the block is rotated, as rotation is only applied to the clone's inner element(s).
		const blockElement = this.cloneWrapper.querySelector( '.wp-block' );

		// We calculate with the block's actual dimensions relative to the page it's on.
		const {
			top: actualTop,
			right: actualRight,
			bottom: actualBottom,
			left: actualLeft,
		} = getRelativeElementPosition( blockElement, parentBlockElement );

		const horizontalCenter = actualLeft + ( ( actualRight - actualLeft ) / 2 );
		const verticalCenter = actualTop + ( ( actualBottom - actualTop ) / 2 );

		const newSnapLines = [];

		const snappingEnabled = ! event.getModifierState( 'Alt' );

		if ( snappingEnabled ) {
			const findSnaps = ( snapKeys, ...values ) => {
				return values
					.map( ( value ) => findClosestSnap( value, snapKeys, BLOCK_DRAGGING_SNAP_GAP ) )
					.filter( ( value ) => value !== null );
			};

			const _horizontalSnaps = findSnaps( this.horizontalSnapKeys, actualLeft, actualRight, horizontalCenter );
			const _verticalSnaps = findSnaps( this.verticalSnapKeys, actualTop, actualBottom, verticalCenter );

			for ( const snap of _horizontalSnaps ) {
				newSnapLines.push( ...this.horizontalSnaps[ snap ] );
			}

			for ( const snap of _verticalSnaps ) {
				newSnapLines.push( ...this.verticalSnaps[ snap ] );
			}
		}

		if ( newSnapLines.length ) {
			setSnapLines( newSnapLines );
		} else if ( snapLines.length ) {
			clearSnapLines();
		}

		// Don't allow the CTA button to go over its top limit.
		if ( 'amp/amp-story-cta' === this.props.blockName ) {
			this.cloneWrapper.style.top = top >= 0 ? `${ top }px` : '0px';
		} else {
			this.cloneWrapper.style.top = `${ top }px`;
		}

		this.cloneWrapper.style.left = `${ left }px`;

		// Update cursor coordinates.
		this.cursorLeft = event.clientX;
		this.cursorTop = event.clientY;

		lastY = top;
		lastX = left;
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
		const {
			blockName,
			elementId,
			transferData,
			onDragStart = noop,
			snapLines,
			showSnapLines,
			clearSnapLines,
			horizontalSnaps,
			verticalSnaps,
		} = this.props;
		const isCTABlock = 'amp/amp-story-cta' === blockName;
		// In the CTA block only the inner element (the button) is draggable, not the whole block.
		const element = isCTABlock ? document.getElementById( elementId ) : document.getElementById( elementId ).parentNode;

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
		const baseHeight = isCTABlock ? STORY_PAGE_INNER_HEIGHT / 5 : STORY_PAGE_INNER_HEIGHT;

		// Position clone over the original element.
		this.cloneWrapper.style.top = `${ getPixelsFromPercentage( 'y', parseInt( clone.style.top ), baseHeight ) }px`;
		this.cloneWrapper.style.left = `${ getPixelsFromPercentage( 'x', parseInt( clone.style.left ), STORY_PAGE_INNER_WIDTH ) }px`;

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

		showSnapLines();
		if ( snapLines.length ) {
			clearSnapLines();
		}

		this.horizontalSnaps = horizontalSnaps();
		this.horizontalSnapKeys = Object.keys( this.horizontalSnaps );
		this.verticalSnaps = verticalSnaps();
		this.verticalSnapKeys = Object.keys( this.verticalSnaps );

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
	elementId: PropTypes.string,
	transferData: PropTypes.shape( {
		type: PropTypes.string,
		srcIndex: PropTypes.number,
		srcRootClientId: PropTypes.string,
		srcClientId: PropTypes.string,
	} ),
	onDragStart: PropTypes.func,
	onDragEnd: PropTypes.func,
	setTimeout: PropTypes.func.isRequired,
	children: PropTypes.node.isRequired,
	horizontalSnaps: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.number ),
		PropTypes.func,
	] ).isRequired,
	verticalSnaps: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.number ),
		PropTypes.func,
	] ).isRequired,
	snapLines: PropTypes.array.isRequired,
	showSnapLines: PropTypes.func.isRequired,
	hideSnapLines: PropTypes.func.isRequired,
	setSnapLines: PropTypes.func.isRequired,
	clearSnapLines: PropTypes.func.isRequired,
	parentBlockElement: PropTypes.object,
};

const enhance = compose(
	withSnapTargets,
	withSafeTimeout,
);

export default enhance( Draggable );
