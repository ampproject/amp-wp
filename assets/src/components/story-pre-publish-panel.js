/**
 * WordPress dependencies
 */
import { Notice } from '@wordpress/components';
import { PluginPrePublishPanel } from '@wordpress/edit-post';
import { Fragment } from '@wordpress/element';
import { withSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { hasMinimumStoryPosterDimensions } from '../helpers';

/**
 * Conditionally adds a notice to the pre-publish panel for the featured image.
 *
 * @return {Function} Either a plain pre-publish panel, or the panel with a featured image notice.
 */
const StoryPrePublishPanel = ( { featuredMedia } ) => {
	if ( featuredMedia && hasMinimumStoryPosterDimensions( featuredMedia ) ) {
		return null;
	}

	const message = ! featuredMedia ? __( 'Selecting a featured image is required.', 'amp' ) : __( 'The featured image must have minimum dimensions of 696px x 928px, 928px x 696px, or 928px x 928px.', 'amp' );

	return (
		<Fragment>
			<PluginPrePublishPanel
				title={ __( 'Featured Image', 'amp' ) }
				initialOpen="true"
			>
				<Notice
					status="warning"
					isDismissible={ false }
				>
					<span>
						{ message }
					</span>
				</Notice>
			</PluginPrePublishPanel>
		</Fragment>
	);
};

export default withSelect( ( select ) => {
	const currentPost = select( 'core/editor' ).getCurrentPost();
	const editedFeaturedMedia = select( 'core/editor' ).getEditedPostAttribute( 'featured_media' );
	const featuredMedia = currentPost.featured_media || editedFeaturedMedia;

	return {
		featuredMedia,
	};
} )( StoryPrePublishPanel );
