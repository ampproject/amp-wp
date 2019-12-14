/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data';

const {	getBlock } = select( 'core/block-editor' );
const { updateBlockAttributes } = dispatch( 'core/block-editor' );

/**
 * Removes a pre-set caption from image and video block.
 *
 * @param {string} clientId Block ID.
 */
const maybeRemoveMediaCaption = ( clientId ) => {
	const block = getBlock( clientId );

	if ( ! block ) {
		return;
	}

	const isImage = 'core/image' === block.name;
	const isVideo = 'core/video' === block.name;

	if ( ! isImage && ! isVideo ) {
		return;
	}

	const { attributes } = block;

	// If we have an image or video with pre-set caption we should remove the caption.
	if (
		( ( ! attributes.ampShowImageCaption && isImage ) || ( ! attributes.ampShowCaption && isVideo ) ) &&
		attributes.caption &&
		0 !== attributes.caption.length
	) {
		updateBlockAttributes( clientId, { caption: '' } );
	}
};

export default maybeRemoveMediaCaption;
