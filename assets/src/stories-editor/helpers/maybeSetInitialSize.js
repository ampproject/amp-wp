/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORY_PAGE_INNER_HEIGHT, STORY_PAGE_INNER_WIDTH } from '../constants';

const { getMedia } = select( 'core' );
const {	getBlock } = select( 'core/block-editor' );
const { updateBlockAttributes } = dispatch( 'core/block-editor' );

/**
 * Sets width and height for blocks if they haven't been modified yet.
 *
 * @param {string} clientId Block ID.
 */
const maybeSetInitialSize = ( clientId ) => {
	const block = getBlock( clientId );

	if ( ! block ) {
		return;
	}

	const { name, attributes } = block;

	if ( 'core/image' !== name ) {
		return;
	}
	const { width, height } = attributes;

	/**
	 * Sets width and height to image if it hasn't been set via resizing yet.
	 *
	 * Takes the values from the original image.
	 */
	if ( ! width && ! height && attributes.id > 0 ) {
		const media = getMedia( attributes.id );
		// If the width and height haven't been set for the media, we should get it from the original image.
		if ( media && media.media_details ) {
			const { height: imageHeight, width: imageWidth } = media.media_details;

			let ratio = 1;
			// If the image exceeds the page limits, adjust the width and height accordingly.
			if ( STORY_PAGE_INNER_WIDTH < imageWidth || STORY_PAGE_INNER_HEIGHT < imageHeight ) {
				ratio = Math.max( imageWidth / STORY_PAGE_INNER_WIDTH, imageHeight / STORY_PAGE_INNER_HEIGHT );
			}

			updateBlockAttributes( clientId, {
				width: Math.round( imageWidth / ratio ),
				height: Math.round( imageHeight / ratio ),
			} );
		}
	}
};

export default maybeSetInitialSize;
