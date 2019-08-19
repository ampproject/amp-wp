/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { ResizableBox } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './edit.css';
import {
	getPercentageFromPixels,
	getPixelsFromPercentage,
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
	REVERSE_WIDTH_CALCULATIONS,
	REVERSE_HEIGHT_CALCULATIONS,
} from '../../constants';

let lastSeenX = 0,
	lastSeenY = 0,
	lastWidth,
	lastHeight,
	blockElement = null,
	blockElementTop,
	blockElementLeft,
	imageWrapper,
	textBlockWrapper,
	textElement;

const EnhancedResizableBox = ( props ) => {
	const {
		isSelected,
		angle,
		blockName,
		ampFitText,
		minWidth,
		minHeight,
		onResizeStart,
		onResizeStop,
		children,
		...otherProps
	} = props;

	let {
		width,
		height,
	} = props;

	const isImage = 'core/image' === blockName;
	const isText = 'amp/amp-story-text' === blockName;

	return (
		<ResizableBox
			{ ...otherProps }
			className={ classnames(
				'amp-story-resize-container',
				{ 'is-selected': isSelected }
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
				blockElement = element.closest( '.wp-block' );
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
					// Text block is handled differently since the text block's content shouldn't have full width while measuring.
					if ( angle ) {
						if ( ! isText ) {
							textElement.style.width = appliedWidth + 'px';
							textElement.style.height = appliedHeight + 'px';
						} else if ( isText && ! ampFitText ) {
							textElement.style.width = 'initial';
						}
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
				}

				if ( ! angle ) {
					// If the resizing is to left or top then we have to compensate
					if ( REVERSE_WIDTH_CALCULATIONS.includes( direction ) ) {
						const leftInPx = getPixelsFromPercentage( 'x', parseFloat( blockElementLeft ) );
						blockElement.style.left = getPercentageFromPixels( 'x', leftInPx - deltaW ) + '%';
					}
					if ( REVERSE_HEIGHT_CALCULATIONS.includes( direction ) ) {
						const topInPx = getPixelsFromPercentage( 'y', parseFloat( blockElementTop ) );
						blockElement.style.top = getPercentageFromPixels( 'y', topInPx - deltaH ) + '%';
					}
				} else {
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

					const originalPos = getResizedBlockPosition( direction, blockElementLeft, blockElementTop, deltaW, deltaH );

					// @todo Figure out why calculating the new top / left position doesn't work in case of small height value.
					// @todo Remove this temporary fix.
					if ( appliedHeight < 60 ) {
						diff.left = diff.left / ( 60 / appliedHeight );
						diff.right = diff.right / ( 60 / appliedHeight );
					}

					const updatedPos = getUpdatedBlockPosition( direction, originalPos, diff );

					blockElement.style.left = getPercentageFromPixels( 'x', updatedPos.left ) + '%';
					blockElement.style.top = getPercentageFromPixels( 'y', updatedPos.top ) + '%';
				}

				element.style.width = appliedWidth + 'px';
				element.style.height = appliedHeight + 'px';

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
			} }
		>
			{ children }
		</ResizableBox>
	);
};

EnhancedResizableBox.propTypes = {
	isSelected: PropTypes.bool,
	ampFitText: PropTypes.bool,
	angle: PropTypes.number,
	blockName: PropTypes.string,
	minWidth: PropTypes.number,
	minHeight: PropTypes.number,
	onResizeStart: PropTypes.func.isRequired,
	onResizeStop: PropTypes.func.isRequired,
	children: PropTypes.any.isRequired,
	width: PropTypes.number,
	height: PropTypes.number,
};

export default EnhancedResizableBox;
