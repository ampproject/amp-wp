/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';

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
