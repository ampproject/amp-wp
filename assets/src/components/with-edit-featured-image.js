/**
 * WordPress dependencies
 */
import { dispatch, withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { hasMinimumStoryPosterDimensions } from './';

/**
 * Gets a wrapped version of a block's edit component that conditionally sets the featured image.
 *
 * On selecting an image, if there is no featured image, set it to the selected image.
 * Only applies to the Image block and the AMP Story Page's 'Background Media' control.
 *
 * @param {Function} BlockEdit A block's edit component, passed from the filter.
 * @return {Function} The BlockEdit component.
 */
export default ( BlockEdit ) => {
	return withSelect( ( select, ownProps ) => {
		const isCorrectBlock = ( 'core/image' === ownProps.name || 'amp/amp-story-page' === ownProps.name );
		if ( ! isCorrectBlock || ! ownProps.attributes ) {
			return;
		}

		const selectedMediaId = ownProps.attributes.mediaId || ownProps.attributes.id;
		const selectedBlock = select( 'core/editor' ).getSelectedBlock();
		if ( ! selectedMediaId || ! selectedBlock || ! selectedBlock.attributes ) {
			return;
		}

		// Check that the media is from the selected block.
		const selectedBlockMediaId = selectedBlock.attributes.mediaId || selectedBlock.attributes.id;
		if ( ! selectedBlockMediaId || selectedBlockMediaId !== selectedMediaId ) {
			return;
		}

		// Conditionally set the selected image as the featured image.
		const media = select( 'core' ).getMedia( selectedMediaId );
		if ( media && media.media_details && hasMinimumStoryPosterDimensions( media.media_details ) ) {
			dispatch( 'core/editor' ).editPost( { featured_media: selectedMediaId } );
		}
	} )( ( props ) => {
		return (
			<BlockEdit { ...props } />
		);
	} );
};
