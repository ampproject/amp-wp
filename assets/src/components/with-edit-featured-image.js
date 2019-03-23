/**
 * WordPress dependencies
 */
import { dispatch, withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { hasMinimumDimensions } from './';

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
		if ( 'core/image' !== ownProps.name && 'amp/amp-story-page' !== ownProps.name ) {
			return;
		}

		// Confirm that this ID is from the selected block.
		const selectedBlock = select( 'core/editor' ).getSelectedBlock();
		if ( ! selectedBlock || ! selectedBlock.attributes.id || ! ownProps.attributes || ownProps.attributes.id !== selectedBlock.attributes.id ) {
			return;
		}

		// Conditionally set the selected image as the featured image.
		const media = select( 'core' ).getMedia( ownProps.attributes.id );
		if ( media && media.media_details && hasMinimumDimensions( media.media_details ) ) {
			dispatch( 'core/editor' ).editPost( { featured_media: ownProps.attributes.id } );
		}
	} )( ( props ) => {
		return (
			<BlockEdit { ...props } />
		);
	} );
};
