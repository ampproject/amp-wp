/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { ResizableBox } from '@wordpress/components';

/**
 * Internal dependencies
 */
import withSnapTargets from '../higher-order/with-snap-targets';
import './edit.css';
import {
	getPercentageFromPixels,
	findClosestSnap, getRelativeElementPosition,
} from '../../helpers';
import {
	getBlockPositioning,
	getResizedBlockPosition,
	getUpdatedBlockPosition,
	getResizedWidthAndHeight,
	getRadianFromDeg,
	getBlockTextElement,
} from './helpers';
import {
	TEXT_BLOCK_PADDING,
	BLOCK_RESIZING_SNAP_GAP,
} from '../../constants';

let lastSeenX = 0,
	lastSeenY = 0,
	lastWidth,
	lastHeight,
	blockElement = null,
	blockElementTop,
	blockElementLeft,
	lastDeltaW,
	lastDeltaH,
	imageWrapper,
	textBlockWrapper,
	textElement;

class EnhancedResizableBox extends Component {
	constructor( ...args ) {
		super( ...args );
		this.state = {
			isResizing: false,
		};
	}

	render() {
		const {
			angle,
			blockName,
			ampFitText,
			minWidth,
			minHeight,
			onResizeStart,
			onResizeStop,
			children,
			...otherProps
		} = this.props;

		let {
			width,
			height,
		} = this.props;

		const { isResizing } = this.state;

		const isImage = 'core/image' === blockName;
		const isText = 'amp/amp-story-text' === blockName;

		// Ensure that these props are not passed down.
		const {
			clientId,
			snapGap,
			horizontalSnaps,
			verticalSnaps,
			snapLines,
			showSnapLines,
			hideSnapLines,
			setSnapLines,
			clearSnapLines,
			parentBlockElement,
			...childProps
		} = otherProps;

		return (
			<ResizableBox
				{ ...childProps }
				className={ classnames(
					'amp-story-resize-container',
					{
						'is-resizing': isResizing,
					},
				) }
				size={ {
					height,
					width,
				} }
				enable={ {
					top: true,
					right: true,
					bottom: true,
					left: true,
					topRight: true,
					bottomRight: true,
					bottomLeft: true,
					topLeft: true,
				} }
				onResizeStop={ ( event, direction ) => {
					const { deltaW, deltaH } = getResizedWidthAndHeight( event, angle, lastSeenX, lastSeenY, direction );
					let appliedWidth = width + deltaW;
					let appliedHeight = height + deltaH;

					// Restore the full height for Text block wrapper.
					if ( textBlockWrapper ) {
						textBlockWrapper.style.height = '100%';
					}

					// Ensure the measures not crossing limits.
					appliedWidth = appliedWidth < lastWidth ? lastWidth : appliedWidth;
					appliedHeight = appliedHeight < lastHeight ? lastHeight : appliedHeight;

					const elementTop = parseFloat( blockElement.style.top );
					const elementLeft = parseFloat( blockElement.style.left );

					const positionTop = Number( elementTop.toFixed( 2 ) );
					const positionLeft = Number( elementLeft.toFixed( 2 ) );

					this.setState( { isResizing: false } );

					hideSnapLines();
					if ( snapLines.length ) {
						clearSnapLines();
					}

					onResizeStop( {
						width: parseInt( appliedWidth ),
						height: parseInt( appliedHeight ),
						positionTop,
						positionLeft,
					} );
				} }
				onResizeStart={ ( event, direction, element ) => {
					lastSeenX = event.clientX;
					lastSeenY = event.clientY;
					lastWidth = width;
					lastHeight = height;
					lastDeltaW = null;
					lastDeltaH = null;
					blockElement = element.closest( '.wp-block' ).parentNode;
					blockElementTop = blockElement.style.top;
					blockElementLeft = blockElement.style.left;
					if ( isImage ) {
						imageWrapper = blockElement.querySelector( 'figure .components-resizable-box__container' );
					}
					textElement = ! ampFitText ? getBlockTextElement( blockName, blockElement ) : null;

					if ( ampFitText && isText ) {
						textBlockWrapper = blockElement.querySelector( '.with-line-height' );
					} else {
						textBlockWrapper = null;
					}

					this.setState( { isResizing: true } );

					showSnapLines();
					if ( snapLines.length ) {
						clearSnapLines();
					}

					this.horizontalSnaps = horizontalSnaps();
					this.horizontalSnapKeys = Object.keys( this.horizontalSnaps );
					this.verticalSnaps = verticalSnaps();
					this.verticalSnapKeys = Object.keys( this.verticalSnaps );

					onResizeStart();
				} }
				onResize={ ( event, direction, element ) => { // eslint-disable-line complexity
					const { deltaW, deltaH } = getResizedWidthAndHeight( event, angle, lastSeenX, lastSeenY, direction );

					// Handle case where media is inserted from URL.
					if ( isImage && ! width && ! height ) {
						width = blockElement.clientWidth;
						height = blockElement.clientHeight;
					}

					let appliedWidth = minWidth <= width + deltaW ? width + deltaW : minWidth;
					let appliedHeight = minHeight <= height + deltaH ? height + deltaH : minHeight;

					const isReducing = 0 > deltaW || 0 > deltaH;

					if ( textElement && isReducing ) {
						// If we have a rotated block, let's assign the width and height for measuring.
						// Without assigning the new measure, the calculation would be incorrect due to angle.
						if ( angle ) {
							textElement.style.width = appliedWidth + 'px';
							textElement.style.height = appliedHeight + 'px';
						}

						// Whenever reducing the size of a text element, set height to `auto`
						// (overwriting the above for angled text boxes) to get proper scroll height.
						if ( isText ) {
							textElement.style.height = 'auto';
						}

						const scrollWidth = textElement.scrollWidth;
						const scrollHeight = textElement.scrollHeight;
						if ( appliedWidth < scrollWidth || appliedHeight < scrollHeight ) {
							appliedWidth = lastWidth;
							appliedHeight = lastHeight;
						}
						// If we have rotated block, let's restore the correct measures.
						if ( angle ) {
							if ( ! isText ) {
								textElement.style.width = 'initial';
								textElement.style.height = '100%';
							} else if ( isText && ! ampFitText ) {
								textElement.style.width = '100%';
							}
						}

						// Reset text element height.
						if ( isText ) {
							textElement.style.height = '';
						}
					}

					// If it's not min width / height yet, assign lastDeltaH and lastDeltaW for position calculation.
					if ( minHeight < appliedHeight ) {
						lastDeltaH = deltaH;
					}
					if ( minWidth < appliedWidth ) {
						lastDeltaW = deltaW;
					}

					const radianAngle = getRadianFromDeg( angle );

					// Compare position between the initial and after resizing.
					let initialPosition, resizedPosition;

					// If it's a text block, we shouldn't consider the added padding for measuring.
					if ( isText ) {
						initialPosition = getBlockPositioning( width - ( TEXT_BLOCK_PADDING * 2 ), height - ( TEXT_BLOCK_PADDING * 2 ), radianAngle, direction );
						resizedPosition = getBlockPositioning( appliedWidth - ( TEXT_BLOCK_PADDING * 2 ), appliedHeight - ( TEXT_BLOCK_PADDING * 2 ), radianAngle, direction );
					} else {
						initialPosition = getBlockPositioning( width, height, radianAngle, direction );
						resizedPosition = getBlockPositioning( appliedWidth, appliedHeight, radianAngle, direction );
					}
					const diff = {
						left: resizedPosition.left - initialPosition.left,
						top: resizedPosition.top - initialPosition.top,
					};

					const originalPos = getResizedBlockPosition( direction, blockElementLeft, blockElementTop, lastDeltaW, lastDeltaH );
					const updatedPos = getUpdatedBlockPosition( direction, originalPos, diff );


					blockElement.style.left = getPercentageFromPixels( 'x', updatedPos.left ) + '%';
					blockElement.style.top = getPercentageFromPixels( 'y', updatedPos.top ) + '%';

					element.style.width = appliedWidth + 'px';
					element.style.height = appliedHeight + 'px';

					// Get the correct dimensions in case the block is rotated, as rotation is only applied to the clone's inner element(s).
					// We calculate with the block's actual dimensions relative to the page it's on.
					const {
						top: actualTop,
						right: actualRight,
						bottom: actualBottom,
						left: actualLeft,
					} = getRelativeElementPosition( blockElement.querySelector( '.wp-block' ), parentBlockElement );

					const horizontalCenter = actualLeft + ( ( actualRight - actualLeft ) / 2 );
					const verticalCenter = actualTop + ( ( actualBottom - actualTop ) / 2 );

					const newSnapLines = [];

					const snappingEnabled = ! event.getModifierState( 'Alt' );

					if ( snappingEnabled ) {
						const findSnaps = ( snapKeys, ...values ) => {
							return values
								.map( ( value ) => findClosestSnap( value, snapKeys, BLOCK_RESIZING_SNAP_GAP ) )
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

					lastWidth = appliedWidth;
					lastHeight = appliedHeight;

					if ( textBlockWrapper ) {
						if ( ampFitText ) {
							textBlockWrapper.style.lineHeight = appliedHeight + 'px';
						}
						// Also add the height to the wrapper since the background color is set to the wrapper.
						textBlockWrapper.style.height = appliedHeight + 'px';
					}

					// If it's image, let's change the width and height of the image, too.
					if ( imageWrapper && isImage ) {
						imageWrapper.style.width = appliedWidth + 'px';
						imageWrapper.style.height = appliedHeight + 'px';
					}

					if ( newSnapLines.length ) {
						setSnapLines( newSnapLines );
					} else if ( snapLines.length ) {
						clearSnapLines();
					}
				} }
			>
				{ children }
			</ResizableBox>
		);
	}
}

EnhancedResizableBox.defaultProps = {
	snapGap: 0,
};

EnhancedResizableBox.propTypes = {
	ampFitText: PropTypes.bool,
	angle: PropTypes.number,
	blockName: PropTypes.string,
	clientId: PropTypes.string,
	minWidth: PropTypes.number,
	minHeight: PropTypes.number,
	onResizeStart: PropTypes.func.isRequired,
	onResizeStop: PropTypes.func.isRequired,
	children: PropTypes.node.isRequired,
	width: PropTypes.number,
	height: PropTypes.number,
	horizontalSnaps: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.number ),
		PropTypes.func,
	] ).isRequired,
	verticalSnaps: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.number ),
		PropTypes.func,
	] ).isRequired,
	snapGap: PropTypes.number.isRequired,
	snapLines: PropTypes.array.isRequired,
	showSnapLines: PropTypes.func.isRequired,
	hideSnapLines: PropTypes.func.isRequired,
	setSnapLines: PropTypes.func.isRequired,
	clearSnapLines: PropTypes.func.isRequired,
	parentBlockElement: PropTypes.object,
};

export default withSnapTargets( EnhancedResizableBox );
