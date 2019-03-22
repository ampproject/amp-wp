/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data';

/**
 * External dependencies
 */
import { delay } from 'lodash';

/**
 * Internal dependencies
 */
import { hasMinimumDimensions } from './';

/**
 * Gets a wrapped version of MediaPlaceholder.
 *
 * On uploading an image, if there is no featured image, set it to the selected image.
 * The InitialMediaPlaceholder is usually the MediaPlaceholder component, unless it was filtered somewhere.
 *
 * @param {Function} initialPlaceholder The MediaPlaceholder component, passed from the filter.
 * @return {Function} The wrapped component.
 */
export default ( initialPlaceholder ) => {
	return class FeaturedImageMediaPlaceholder extends initialPlaceholder {
		/**
		 * Construct the class.
		*/
		constructor() {
			super( ...arguments );
			this.componentWillUnmount = this.componentWillUnmount.bind( this );
		}

		/**
		 * The handler for before the component unmounts.
		 *
		 * If there's no featured image, set this image as the featured image.
		 * This component unmounts right after uploading an image.
		 * But this needs to be delayed, as there's an apiFetch() call that gets the id.
		 */
		componentWillUnmount() {
			const featuredMedia = select( 'core/editor' ).getEditedPostAttribute( 'featured_media' );
			if ( featuredMedia ) {
				return;
			}

			delay( () => {
				const selectedBlock = select( 'core/editor' ).getSelectedBlock();
				if ( ! selectedBlock || ! selectedBlock.attributes.id ) {
					return;
				}

				// Conditionally set the uploaded image as the featured image.
				const media = select( 'core' ).getMedia( selectedBlock.attributes.id );
				if ( media && media.media_details && hasMinimumDimensions( media.media_details ) && 'core/image' === selectedBlock.name ) {
					dispatch( 'core/editor' ).editPost( { featured_media: selectedBlock.attributes.id } );
				}
			}, 1000 );
		}
	};
};
