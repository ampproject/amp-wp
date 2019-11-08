/**
 * WordPress dependencies
 */
import { dispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import getBlockInnerTextElement from './getBlockInnerTextElement';
import {
	getPositionAfterResizing,
} from './../components/resizable-box/helpers';
import { getPercentageFromPixels } from './';

const { updateBlockAttributes } = dispatch( 'core/block-editor' );

/**
 * Updates a block's width and height in case it doesn't use amp-fit-text and the font size has changed.
 *
 * @param {Object}  block                         Block object.
 * @param {string}  block.clientId                Block client ID.
 * @param {Object}  block.attributes              Block attributes.
 * @param {number}  block.attributes.width        Block width in pixels.
 * @param {number}  block.attributes.height       Block height in pixels.
 * @param {string}  block.attributes.content      Block inner content.
 * @param {boolean} block.attributes.ampFitText   Whether amp-fit-text should be used or not.
 * @param {number}  block.attributes.autoFontSize Automatically determined font size for amp-fit-text blocks.
 * @param {number}  block.attributes.isPasted     Block has been pasted from clipboard.
 */
const maybeUpdateBlockDimensions = ( block ) => { // eslint-disable-line complexity
	const { name, clientId, attributes } = block;
	const { width, height, ampFitText, content, rotationAngle, positionLeft, positionTop, isPasted } = attributes;

	if ( ampFitText ) {
		return;
	}

	let newHeight,
		newWidth;

	switch ( name ) {
		case 'amp/amp-story-text':
			const element = getBlockInnerTextElement( block );

			if ( element && content.length ) {
				// If the scroll height or width exceeds the actual width/height.
				newHeight = element.scrollHeight > height ? element.scrollHeight : null;
				newWidth = element.scrollWidth > width ? element.scrollWidth : null;
			}

			break;

		case 'amp/amp-story-post-title':
		case 'amp/amp-story-post-author':
		case 'amp/amp-story-post-date':
			const metaBlockElement = getBlockInnerTextElement( block );

			if ( metaBlockElement ) {
				metaBlockElement.classList.toggle( 'is-measuring' );

				// If the scroll height or width exceeds the actual width/height.
				newHeight = metaBlockElement.offsetHeight > height ? metaBlockElement.offsetHeight : null;
				newWidth = metaBlockElement.offsetWidth > width ? metaBlockElement.offsetWidth : null;

				metaBlockElement.classList.toggle( 'is-measuring' );
			}

			break;

		default:
			break;
	}

	// If the block is rotated or text has been pasted and either new width or height has been assigned
	// we need to reposition the block.
	if ( ( rotationAngle || isPasted ) && ( newWidth || newHeight ) ) {
		const deltaW = newWidth ? newWidth - width : 0;
		const deltaH = newHeight ? newHeight - height : 0;
		const { left: newLeft, top: newTop } = getPositionAfterResizing( {
			direction: 'bottomRight',
			angle: rotationAngle,
			isText: 'amp/amp-story-text' === name,
			oldWidth: width,
			oldHeight: height,
			newWidth: width + deltaW,
			newHeight: height + deltaH,
			oldPositionLeft: positionLeft,
			oldPositionTop: positionTop,
		} );
		const newAtts = {
			positionLeft: Number( getPercentageFromPixels( 'x', newLeft ).toFixed( 2 ) ),
			positionTop: Number( getPercentageFromPixels( 'y', newTop ).toFixed( 2 ) ),
		};
		if ( newHeight ) {
			newAtts.height = newHeight;
		}
		if ( newWidth ) {
			newAtts.width = newWidth;
		}
		updateBlockAttributes( clientId, newAtts );
	}
};

export default maybeUpdateBlockDimensions;
