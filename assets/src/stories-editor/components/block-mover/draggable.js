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
import { withSelect } from '@wordpress/data';
import { withSafeTimeout, compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import withSnapTargets from '../higher-order/with-snap-targets';
import {
	getPixelsFromPercentage,
	getPercentageFromPixels,
	getRelativeElementPosition,
	isCTABlock,
} from '../../helpers';
import { getBestSnapLines } from '../../helpers/snapping';
import {
	STORY_PAGE_INNER_WIDTH,
	STORY_PAGE_INNER_HEIGHT,
	BLOCK_DRAGGING_SNAP_GAP,
	STORY_PAGE_INNER_HEIGHT_FOR_CTA,
	STORY_PAGE_MARGIN,
} from '../../constants';

const PAGE_AND_MARGIN = STORY_PAGE_INNER_WIDTH + STORY_PAGE_MARGIN;

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
		const {
			clearHighlight,
			dropElementByOffset,
			blockName,
			setTimeout,
			clearSnapLines,
			isRTL,
			onDragEnd = noop,
		} = this.props;
		if ( event ) {
			event.preventDefault();
		}

		// Make sure to clear highlight.
		clearHighlight();

		// Attempt drop on neighbor if offset
		if ( this.pageOffset !== 0 ) {
			// All this is about calculating the position of the (correct) element on the new page.
			const currentElementTop = parseInt( this.cloneWrapper.style.top );
			const currentElementLeft = parseInt( this.cloneWrapper.style.left );
			const factor = isRTL ? 1 : -1;
			const newLeft = currentElementLeft + factor * ( this.pageOffset * PAGE_AND_MARGIN );

			let baseHeight, xAttribute, yAttribute;
			if ( isCTABlock( blockName ) ) {
				baseHeight = STORY_PAGE_INNER_HEIGHT_FOR_CTA;
				xAttribute = 'btnPositionLeft';
				yAttribute = 'btnPositionTop';
			} else {
				baseHeight = STORY_PAGE_INNER_HEIGHT;
				xAttribute = 'positionLeft';
				yAttribute = 'positionTop';
			}

			const newAttributes = {
				[ xAttribute ]: getPercentageFromPixels( 'x', newLeft, STORY_PAGE_INNER_WIDTH ),
				[ yAttribute ]: getPercentageFromPixels( 'y', currentElementTop, baseHeight ),
			};
			dropElementByOffset( this.pageOffset, newAttributes );
		}

		this.resetDragState();

		clearSnapLines();

		setTimeout( onDragEnd );
	}

	/**
	 * Updates positioning of element clone based on mouse movement during dragging.
	 *
	 * @param  {Object} event The non-custom DragEvent.
	 */
	onDragOver = ( event ) => { // eslint-disable-line complexity
		const {
			blockName,
			setSnapLines,
			clearSnapLines,
			parentBlockElement,
			horizontalTargets,
			verticalTargets,
			setHighlightByOffset,
			isRTL,
		} = this.props;

		const top = parseInt( this.cloneWrapper.style.top ) + event.clientY - this.cursorTop;
		const left = parseInt( this.cloneWrapper.style.left ) + event.clientX - this.cursorLeft;

		if ( top === lastY && left === lastX ) {
			return;
		}

		// Get the correct dimensions in case the block is rotated, as rotation is only applied to the clone's inner element(s).
		// For CTA blocks, not the whole block is draggable, but only the button within.
		const blockElement = isCTABlock( blockName ) ? this.cloneWrapper.querySelector( '.amp-story-cta-button' ) : this.cloneWrapper.querySelector( '.wp-block' );

		// We calculate with the block's actual dimensions relative to the page it's on.
		const {
			top: actualTop,
			right: actualRight,
			bottom: actualBottom,
			left: actualLeft,
		} = getRelativeElementPosition( blockElement, parentBlockElement );

		const snappingEnabled = ! event.getModifierState( 'Alt' );

		if ( snappingEnabled ) {
			const [ horizontalEdgeSnaps, horizontalCenterSnaps ] = horizontalTargets( actualTop, actualBottom );
			const [ verticalEdgeSnaps, verticalCenterSnaps ] = verticalTargets( actualLeft, actualRight );
			setSnapLines( [
				...getBestSnapLines( horizontalEdgeSnaps, horizontalCenterSnaps, actualLeft, actualRight, BLOCK_DRAGGING_SNAP_GAP ),
				...getBestSnapLines( verticalEdgeSnaps, verticalCenterSnaps, actualTop, actualBottom, BLOCK_DRAGGING_SNAP_GAP ),
			] );
		} else {
			clearSnapLines();
		}

		// Don't allow the CTA button to go over its top limit.
		if ( isCTABlock( blockName ) ) {
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

			if ( isRTL ) {
				this.pageOffset *= -1;
			}
		}

		setHighlightByOffset( this.pageOffset );
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
			clearSnapLines,
		} = this.props;
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
		const baseHeight = blockIsCTA ? STORY_PAGE_INNER_HEIGHT_FOR_CTA : STORY_PAGE_INNER_HEIGHT;

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

		clearSnapLines();

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
	clearHighlight: PropTypes.func.isRequired,
	setHighlightByOffset: PropTypes.func.isRequired,
	dropElementByOffset: PropTypes.func.isRequired,
	setTimeout: PropTypes.func.isRequired,
	children: PropTypes.func.isRequired,
	horizontalTargets: PropTypes.func.isRequired,
	verticalTargets: PropTypes.func.isRequired,
	setSnapLines: PropTypes.func.isRequired,
	clearSnapLines: PropTypes.func.isRequired,
	parentBlockElement: PropTypes.object,
	isRTL: PropTypes.bool.isRequired,
};

const enhance = compose(
	withSnapTargets,
	withSafeTimeout,
	withSelect( ( select ) => {
		return {
			isRTL: select( 'core/block-editor' ).getSettings().isRTL,
		};
	} ),
);

export default enhance( Draggable );
