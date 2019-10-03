/**
 * WordPress dependencies
 */
import { dispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import getBlockInnerTextElement from './getBlockInnerTextElement';

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
 */
const maybeUpdateBlockDimensions = ( block ) => {
	const { name, clientId, attributes } = block;
	const { width, height, ampFitText, content } = attributes;

	if ( ampFitText ) {
		return;
	}

	switch ( name ) {
		case 'amp/amp-story-text':
			const element = getBlockInnerTextElement( block );

			if ( element && content.length ) {
				if ( element.scrollHeight > height ) {
					updateBlockAttributes( clientId, { height: element.scrollHeight } );
				}

				if ( element.scrollWidth > width ) {
					updateBlockAttributes( clientId, { width: element.scrollWidth } );
				}
			}

			break;

		case 'amp/amp-story-post-title':
		case 'amp/amp-story-post-author':
		case 'amp/amp-story-post-date':
			const metaBlockElement = getBlockInnerTextElement( block );

			if ( metaBlockElement ) {
				metaBlockElement.classList.toggle( 'is-measuring' );

				if ( metaBlockElement.offsetHeight > height ) {
					updateBlockAttributes( clientId, { height: metaBlockElement.offsetHeight } );
				}

				if ( metaBlockElement.offsetWidth > width ) {
					updateBlockAttributes( clientId, { width: metaBlockElement.offsetWidth } );
				}

				metaBlockElement.classList.toggle( 'is-measuring' );
			}

			break;

		default:
			break;
	}
};

export default maybeUpdateBlockDimensions;
