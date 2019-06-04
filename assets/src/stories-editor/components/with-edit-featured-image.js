/**
 * WordPress dependencies
 */
import { dispatch, withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getMinimumStoryPosterDimensions } from '../helpers';
import { hasMinimumDimensions, getMinimumFeaturedImageDimensions } from '../../common/helpers';

/**
 * Gets a wrapped version of a block's edit component that conditionally sets the featured image (only for AMP Story posts).
 *
 * On selecting an image, if there is no featured image, set it to the selected image.
 * Only applies to the Image block and the AMP Story Page's 'Background Media' control.
 *
 * @param {Function} BlockEdit A block's edit component, passed from the filter.
 * @return {Function} The BlockEdit component.
 */
export default ( BlockEdit ) => {
	return withSelect( ( select, ownProps ) => {
		const { getMedia } = select( 'core' );
		const { getEditedPostAttribute } = select( 'core/editor' );
		const { getSelectedBlock } = select( 'core/block-editor' );
		const { editPost } = dispatch( 'core/editor' );

		const featuredImage = getEditedPostAttribute( 'featured_media' );
		const isRelevantBlock = ( 'core/image' === ownProps.name || 'amp/amp-story-page' === ownProps.name );

		if ( featuredImage || ! isRelevantBlock || ! ownProps.attributes ) {
			return;
		}

		const selectedMediaId = ownProps.attributes.mediaId || ownProps.attributes.id;
		const selectedBlock = getSelectedBlock();
		if ( ! selectedMediaId || ! selectedBlock || ! selectedBlock.attributes ) {
			return;
		}

		// Check that the media is from the selected block.
		const selectedBlockMediaId = selectedBlock.attributes.mediaId || selectedBlock.attributes.id;
		if ( ! selectedBlockMediaId || selectedBlockMediaId !== selectedMediaId ) {
			return;
		}

		// Conditionally set the selected image as the featured image.
		const media = getMedia( selectedMediaId );
		if (
			media && media.media_details &&
			hasMinimumDimensions( media.media_details, getMinimumFeaturedImageDimensions() ) &&
			hasMinimumDimensions( media.media_details, getMinimumStoryPosterDimensions() )
		) {
			editPost( { featured_media: selectedMediaId } );
		}
	} )( BlockEdit );
};
