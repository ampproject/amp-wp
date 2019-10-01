/**
 * WordPress dependencies
 */
import { dispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import calculateFontSize from './calculateFontSize';
import getBlockInnerTextElement from './getBlockInnerTextElement';
import { MAX_FONT_SIZE, MIN_FONT_SIZE } from '../../common/constants';

const { updateBlockAttributes } = dispatch( 'core/block-editor' );

/**
 * Updates a block's font size in case it uses amp-fit-text and the content has changed.
 *
 * @param {Object}  block                         Block object.
 * @param {string}  block.clientId                Block client ID.
 * @param {Object}  block.attributes              Block attributes.
 * @param {number}  block.attributes.width        Block width in pixels.
 * @param {number}  block.attributes.height       Block height in pixels.
 * @param {string}  block.attributes.content      Block inner content.
 * @param {boolean} block.attributes.ampFitText   Whether amp-fit-text should be used or not.
 * @param {number}  block.attributes.autoFontSize Automatically determined font size for amp-fit-text blocks.
 */
const maybeUpdateFontSize = ( block ) => {
	const { name, clientId, attributes } = block;
	const { width, height, ampFitText, content, autoFontSize } = attributes;

	if ( ! ampFitText ) {
		return;
	}

	switch ( name ) {
		case 'amp/amp-story-text':
			const element = getBlockInnerTextElement( block );

			if ( element && content.length ) {
				const fitFontSize = calculateFontSize( element, height, width, MAX_FONT_SIZE, MIN_FONT_SIZE );

				if ( fitFontSize && autoFontSize !== fitFontSize ) {
					updateBlockAttributes( clientId, { autoFontSize: fitFontSize } );
				}
			}

			break;

		case 'amp/amp-story-post-title':
		case 'amp/amp-story-post-author':
		case 'amp/amp-story-post-date':
			const metaBlockElement = getBlockInnerTextElement( block );

			if ( metaBlockElement ) {
				const fitFontSize = calculateFontSize( metaBlockElement, height, width, MAX_FONT_SIZE, MIN_FONT_SIZE );
				if ( fitFontSize && autoFontSize !== fitFontSize ) {
					updateBlockAttributes( clientId, { autoFontSize: fitFontSize } );
				}
			}

			break;

		default:
			break;
	}
};

export default maybeUpdateFontSize;
