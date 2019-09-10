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
import { compose, withSafeTimeout } from '@wordpress/compose';
import isShallowEqual from '@wordpress/is-shallow-equal';

/**
 * Internal dependencies
 */
import withSnapTargets from '../higher-order/with-snap-targets';
import {
	getPixelsFromPercentage,
	findClosestSnap,
} from '../../helpers';
import {
	STORY_PAGE_INNER_WIDTH,
	STORY_PAGE_INNER_HEIGHT,
	BLOCK_DRAGGING_SNAP_GAP,
} from '../../constants';

const { Image } = window;

const cloneWrapperClass = 'components-draggable__clone';

const isChromeUA = ( ) => /Chrome/i.test( window.navigator.userAgent );
const documentHasIframes = ( ) => [ ...document.getElementById( 'editor' ).querySelectorAll( 'iframe' ) ].length > 0;

let lastX;
let lastY;
let originalX;
let originalY;
let initialBlockX;
let initialBlockY;

class Draggable extends Component {
	constructor( ...args ) {
		super( ...args );

		this.onDragStart = this.onDragStart.bind( this );
		this.onDragOver = this.onDragOver.bind( this );
		this.onDrop = this.onDrop.bind( this );
		this.onDragEnd = this.onDragEnd.bind( this );
		this.resetDragState = this.resetDragState.bind( this );

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
			parentBlockOffsetTop,
			parentBlockOffsetLeft,
		} = this.props;

		const newSnapLines = [];

		let top = parseInt( this.cloneWrapper.style.top ) + event.clientY - this.cursorTop;
		let left = parseInt( this.cloneWrapper.style.left ) + event.clientX - this.cursorLeft;

		const originalTop = top;
		const originalLeft = left;

		if ( top === lastY && left === lastX ) {
			return;
		}

		lastY = top;
		lastX = left;

		const dimensions = this.cloneWrapper.getBoundingClientRect();

		// We calculate with the block's actual dimensions relative to the page it's on.
		let {
			top: actualTop,
			right: actualRight,
			bottom: actualBottom,
			left: actualLeft,
		} = dimensions;

		actualTop -= parentBlockOffsetTop;
		actualRight -= parentBlockOffsetLeft;
		actualBottom -= parentBlockOffsetTop;
		actualLeft -= parentBlockOffsetLeft;

		const {
			width: actualWidth,
			height: actualHeight,
		} = dimensions;

		const horizontalCenter = actualLeft + ( ( actualRight - actualLeft ) / 2 );
		const verticalCenter = actualTop + ( ( actualBottom - actualTop ) / 2 );

		// The difference in width/height caused by rotation.
		const rotatedWidthDiff = ( this.cloneWrapper.offsetWidth - actualWidth ) / 2;
		const rotatedHeightDiff = ( this.cloneWrapper.offsetHeight - actualHeight ) / 2;

		const horizontalLeftSnap = findClosestSnap( actualLeft, this.horizontalSnaps, BLOCK_DRAGGING_SNAP_GAP );
		const horizontalRightSnap = findClosestSnap( actualRight, this.horizontalSnaps, BLOCK_DRAGGING_SNAP_GAP );
		const horizontalCenterSnap = findClosestSnap( horizontalCenter, this.horizontalSnaps, BLOCK_DRAGGING_SNAP_GAP );
		const verticalTopSnap = findClosestSnap( actualTop, this.verticalSnaps, BLOCK_DRAGGING_SNAP_GAP );
		const verticalBottomSnap = findClosestSnap( actualBottom, this.verticalSnaps, BLOCK_DRAGGING_SNAP_GAP );
		const verticalCenterSnap = findClosestSnap( verticalCenter, this.verticalSnaps, BLOCK_DRAGGING_SNAP_GAP );

		const snappingEnabled = ! event.getModifierState( 'Alt' );

		// @todo: Rely on withSnapTargets to provide the data for the snapping lines so this isn't a concern of this component.

		// What the cursor has moved since the beginning.
		const leftDiff = event.clientX - originalX;
		const topDiff = event.clientY - originalY;
		// Where the original block would be positioned based on that.
		const leftToCompareWith = initialBlockX + leftDiff;
		const topToCompareWith = initialBlockY + topDiff;

		if ( horizontalLeftSnap !== null ) {
			const snapLine = [ [ horizontalLeftSnap, 0 ], [ horizontalLeftSnap, STORY_PAGE_INNER_HEIGHT ] ];
			newSnapLines.push( snapLine );

			if ( snappingEnabled ) {
				if ( Math.abs( leftToCompareWith - horizontalLeftSnap ) <= BLOCK_DRAGGING_SNAP_GAP ) {
					left = horizontalLeftSnap - rotatedWidthDiff;
				}
			}
		}

		if ( horizontalRightSnap !== null ) {
			const snapLine = [ [ horizontalRightSnap, 0 ], [ horizontalRightSnap, STORY_PAGE_INNER_HEIGHT ] ];
			newSnapLines.push( snapLine );

			if ( snappingEnabled ) {
				if ( Math.abs( leftToCompareWith + actualWidth - horizontalRightSnap ) <= BLOCK_DRAGGING_SNAP_GAP ) {
					left = horizontalRightSnap - actualWidth;
				}
			}
		}

		if ( horizontalCenterSnap !== null ) {
			const snapLine = [ [ horizontalCenterSnap, 0 ], [ horizontalCenterSnap, STORY_PAGE_INNER_HEIGHT ] ];
			newSnapLines.push( snapLine );

			if ( snappingEnabled ) {
				if ( Math.abs( leftToCompareWith + ( actualWidth / 2 ) - horizontalCenterSnap ) <= BLOCK_DRAGGING_SNAP_GAP ) {
					left = originalLeft - ( horizontalCenter - horizontalCenterSnap );
				}
			}
		}

		if ( verticalTopSnap !== null ) {
			const snapLine = [ [ 0, verticalTopSnap ], [ STORY_PAGE_INNER_WIDTH, verticalTopSnap ] ];
			newSnapLines.push( snapLine );

			if ( snappingEnabled ) {
				if ( Math.abs( topToCompareWith - verticalTopSnap ) <= BLOCK_DRAGGING_SNAP_GAP ) {
					top = verticalTopSnap - rotatedHeightDiff;
				}
			}
		}

		if ( verticalBottomSnap !== null ) {
			const snapLine = [ [ 0, verticalBottomSnap ], [ STORY_PAGE_INNER_WIDTH, verticalBottomSnap ] ];
			newSnapLines.push( snapLine );

			if ( snappingEnabled ) {
				if ( Math.abs( topToCompareWith + actualHeight - verticalBottomSnap ) <= BLOCK_DRAGGING_SNAP_GAP ) {
					top = originalTop - ( actualBottom - verticalBottomSnap );
				}
			}
		}

		if ( verticalCenterSnap !== null ) {
			const snapLine = [ [ 0, verticalCenterSnap ], [ STORY_PAGE_INNER_WIDTH, verticalCenterSnap ] ];
			newSnapLines.push( snapLine );

			if ( snappingEnabled ) {
				if ( Math.abs( topToCompareWith + ( actualHeight / 2 ) - verticalCenterSnap ) <= BLOCK_DRAGGING_SNAP_GAP ) {
					top = originalTop - ( verticalCenter - verticalCenterSnap );
				}
			}
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

		const hasSnapLine = ( item ) => snapLines.find( ( snapLine ) => isShallowEqual( item[ 0 ], snapLine[ 0 ] ) && isShallowEqual( item[ 1 ], snapLine[ 1 ] ) );

		if ( newSnapLines.length ) {
			if ( ! newSnapLines.every( hasSnapLine ) ) {
				setSnapLines( ...newSnapLines );
			}
		} else if ( snapLines.length ) {
			clearSnapLines();
		}
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
		const element = document.getElementById( elementId );
		const isCTABlock = 'amp/amp-story-cta' === blockName;
		const parentPage = element.closest( 'div[data-type="amp/amp-story-page"]' );
		if ( ! element || ! parentPage ) {
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
		initialBlockX = getPixelsFromPercentage( 'x', parseInt( clone.style.left ), STORY_PAGE_INNER_WIDTH );
		initialBlockY = getPixelsFromPercentage( 'y', parseInt( clone.style.top ), STORY_PAGE_INNER_HEIGHT );

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
		originalX = event.clientX;
		originalY = event.clientY;
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
		this.verticalSnaps = verticalSnaps();

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
	clientId: PropTypes.string.isRequired,
	blockName: PropTypes.string,
	elementId: PropTypes.string,
	transferData: PropTypes.object,
	onDragStart: PropTypes.func,
	onDragEnd: PropTypes.func,
	clearTimeout: PropTypes.func.isRequired,
	setTimeout: PropTypes.func.isRequired,
	children: PropTypes.any.isRequired,
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
	parentBlockOffsetTop: PropTypes.number.isRequired,
	parentBlockOffsetLeft: PropTypes.number.isRequired,
};

const enhance = compose(
	withSnapTargets,
	withSafeTimeout,
);

export default enhance( Draggable );
