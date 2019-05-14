/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { ResizableBox } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './edit.css';
import {
	getDelta,
	getPercentageFromPixels,
	getPixelsFromPercentage,
	getBlockPositioning,
} from '../../stories-editor/helpers';

let lastSeenX = 0,
	lastSeenY = 0,
	blockElement = null,
	blockElementTop,
	blockElementLeft;

export default ( props ) => {
	const { isSelected, angle, width, height, minWidth, minHeight, onResizeStart, onResizeStop, children, ...otherProps } = props;

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
				const { deltaW, deltaH } = getDelta( event, angle, lastSeenX, lastSeenY, direction );
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
				onResizeStart();
			} }
			onResize={ ( event, direction, element ) => {
				const { deltaW, deltaH } = getDelta( event, angle, lastSeenX, lastSeenY, direction );

				const appliedWidth = minWidth <= width + deltaW ? width + deltaW : minWidth;
				const appliedHeight = minHeight <= height + deltaH ? height + deltaH : minHeight;

				if ( angle ) {
					//Convert angle from degrees to radians
					const radianAngle = angle * Math.PI / 180;

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
					const updatedPos = {
						left: originalPos.left - diff.left,
						top: originalPos.top + diff.top,
					};

					blockElement.style.left = getPercentageFromPixels( 'x', updatedPos.left ) + '%';
					blockElement.style.top = getPercentageFromPixels( 'y', updatedPos.top ) + '%';
				}

				/*if ( 0 && angle ) {
					switch ( direction ) {
						case 'bottom':
							// const shiftX = ( ( event.clientX - lastSeenX ) / 2 ) * Math.sin( angle );
							// const shiftY = ( ( event.clientY - lastSeenY ) / 2 ) * Math.cos( angle );
							// In case of moving from bottom: Y with plus and X with minus.
							// Shift Y is cos( angle ) * ( deltaH / 2 ); Shift X is sin( angle ) * (deltaH / 2 ).

							const shiftX = getPercentageFromPixels( 'x', ( event.clientX - lastSeenX ) / 2 );
							// const shiftY = getPercentageFromPixels( 'y',( event.clientY - lastSeenY ) / 2 );

							// blockElement.style.top = parseInt( blockElementTop, 10 ) + shiftY + '%';
							 blockElement.style.left = parseInt( blockElementLeft, 10 ) + shiftX + '%';

							break;
						case 'right':
							// In case of moving from right, x with plus and Y with minus.
							// Shift Y is sin( angle ) * (deltaWidth / 2); Shift X is cos( angle ) * (deltaW / 2 ).
							// const shiftX = getPercentageFromPixels( 'x', ( deltaW / 2 ) * Math.cos( angle ) );
							const shiftY = getPercentageFromPixels( 'y', ( deltaH / 2 ) * Math.sin( angle ) );

							// @todo This is not correct calculation.
							blockElement.style.top = parseInt( blockElementTop, 10 ) + shiftY + '%';
							// blockElement.style.left = parseInt( blockElementLeft, 10 ) + shiftX + '%';
							break;
					}
				}*/
				element.style.width = appliedWidth + 'px';
				element.style.height = appliedHeight + 'px';
			} }
		>
			{ children }
		</ResizableBox>
	);
};
