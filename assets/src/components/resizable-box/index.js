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
	getResizedWidthAndHeight,
	getPercentageFromPixels,
	getPixelsFromPercentage,
	getBlockPositioning,
	getRadianFromDeg,
} from '../../stories-editor/helpers';

let lastSeenX = 0,
	lastSeenY = 0,
	blockElement = null,
	blockElementTop,
	blockElementLeft,
	imageWrapper;

const EnhancedResizableBox = ( props ) => {
	const {
		isSelected,
		angle,
		blockName,
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
			// Adding only right and bottom since otherwise it needs to change the top and left position, too.
			enable={ {
				top: false,
				right: true,
				bottom: true,
				left: false,
			} }
			onResizeStop={ ( event, direction ) => {
				const { deltaW, deltaH } = getResizedWidthAndHeight( event, angle, lastSeenX, lastSeenY, direction );
				onResizeStop( {
					width: parseInt( width + deltaW, 10 ),
					height: parseInt( height + deltaH, 10 ),
					positionTop: parseInt( blockElement.style.top, 10 ),
					positionLeft: parseInt( blockElement.style.left, 10 ),
				} );
			} }
			onResizeStart={ ( event, direction, element ) => {
				lastSeenX = event.clientX;
				lastSeenY = event.clientY;
				blockElement = element.closest( '.wp-block' );
				blockElementTop = blockElement.style.top;
				blockElementLeft = blockElement.style.left;
				if ( isImage ) {
					imageWrapper = blockElement.querySelector( 'figure .components-resizable-box__container' );
				}
				onResizeStart();
			} }
			onResize={ ( event, direction, element ) => {
				const { deltaW, deltaH } = getResizedWidthAndHeight( event, angle, lastSeenX, lastSeenY, direction );

				// Handle case where media is inserted from URL.
				if ( isImage && ! width && ! height ) {
					width = blockElement.clientWidth;
					height = blockElement.clientHeight;
				}
				const appliedWidth = minWidth <= width + deltaW ? width + deltaW : minWidth;
				const appliedHeight = minHeight <= height + deltaH ? height + deltaH : minHeight;

				if ( angle ) {
					const radianAngle = getRadianFromDeg( angle );

					// Compare position between the initial and after resizing.
					const initialPosition = getBlockPositioning( width, height, radianAngle );
					const resizedPosition = getBlockPositioning( appliedWidth, appliedHeight, radianAngle );
					const diff = {
						left: resizedPosition.left - initialPosition.left,
						top: resizedPosition.top - initialPosition.top,
					};
					// Get new position based on the difference.
					const originalPos = {
						left: getPixelsFromPercentage( 'x', parseInt( blockElementLeft, 10 ) ),
						top: getPixelsFromPercentage( 'y', parseInt( blockElementTop, 10 ) ),
					};

					// @todo Figure out why calculating the new top / left position doesn't work in case of small height value.
					// @todo Remove this temporary fix.
					if ( appliedHeight < 60 ) {
						diff.left = diff.left / ( 60 / appliedHeight );
						diff.right = diff.right / ( 60 / appliedHeight );
					}

					const updatedPos = {
						left: originalPos.left - diff.left,
						top: originalPos.top + diff.top,
					};

					blockElement.style.left = getPercentageFromPixels( 'x', updatedPos.left ) + '%';
					blockElement.style.top = getPercentageFromPixels( 'y', updatedPos.top ) + '%';
				}

				element.style.width = appliedWidth + 'px';
				element.style.height = appliedHeight + 'px';
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
	angle: PropTypes.number,
	blockName: PropTypes.string,
	minWidth: PropTypes.number,
	minHeight: PropTypes.number,
	onResizeStart: PropTypes.func.isRequired,
	onResizeStop: PropTypes.func.isRequired,
	children: PropTypes.object.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
};

export default EnhancedResizableBox;
