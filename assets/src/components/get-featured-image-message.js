/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';

/**
 * Gets the message for an invalid featured image, if it is invalid.
 *
 * @param {Function} validateImageSize A function to validate whether the size is correct.
 * @param {string} invalidSizeMessage A message to display in a Notice if the size is wrong.
 * @return {string|null} A message about the invalid featured image, or null if it's valid.
 */
export default ( validateImageSize, invalidSizeMessage ) => {
	const currentPost = select( 'core/editor' ).getCurrentPost();
	const editedFeaturedMedia = select( 'core/editor' ).getEditedPostAttribute( 'featured_media' );
	const featuredMedia = currentPost.featured_media || editedFeaturedMedia;

	if ( ! featuredMedia ) {
		return __( 'Selecting a featured image is required.', 'amp' );
	}

	const media = select( 'core' ).getMedia( featuredMedia );
	if ( ! media || ! media.media_details || ! validateImageSize( media.media_details ) ) {
		return invalidSizeMessage;
	}
};
