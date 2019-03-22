/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { hasMinimumDimensions } from './';

/**
 * Gets a wrapped version of MediaUpload.
 *
 * On selecting an image, if there is no featured image, set it to the selected image.
 * The passed InitialMediaUpload is usually the MediaUpload component, unless it was filtered somewhere.
 *
 * @param {Function} InitialMediaUpload The MediaUpload component, passed from the filter.
 * @return {Function} The wrapped component.
 */
export default ( InitialMediaUpload ) => {
	return class FeaturedImageMediaUpload extends InitialMediaUpload {
		/**
		 * Constructs the class.
		 */
		constructor() {
			super( ...arguments );
			this.onSelect = this.onSelect.bind( this );
		}

		/**
		 * Handles the Media Library frame's 'select' action, mainly copied from MediaUpload.onSelect().
		 */
		onSelect() {
			const { onSelect, multiple = false } = this.props;

			// Get the media information from the Media Library frame.
			const attachment = this.frame.state().get( 'selection' ).toJSON();
			const media = multiple ? attachment : attachment[ 0 ];
			onSelect( media );

			if ( ! hasMinimumDimensions( media ) ) {
				return;
			}

			const featuredMedia = select( 'core/editor' ).getEditedPostAttribute( 'featured_media' );

			// If the post (story) has no featured image, set it to the image that was just selected.
			if ( ! featuredMedia && media.id && 'image' === media.type ) {
				dispatch( 'core/editor' ).editPost( { featured_media: media.id } );
			}
		}
	};
};
