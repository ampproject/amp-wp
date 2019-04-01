/**
 * WordPress dependencies
 */
import { Notice } from '@wordpress/components';
import { PluginPrePublishPanel } from '@wordpress/edit-post';
import { Fragment } from '@wordpress/element';
import { withSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Conditionally adds a notice to the pre-publish panel for the featured image.
 *
 * @return {Function} Either a plain pre-publish panel, or the panel with a featured image notice.
 */
const PrePublishPanel = ( { featuredMedia, validationCallback, missingMediaMessage, invalidMediaMessage, status } ) => {
	if ( featuredMedia && validationCallback( featuredMedia ) ) {
		return null;
	}

	const message = ! featuredMedia ? missingMediaMessage : invalidMediaMessage;

	return (
		<Fragment>
			<PluginPrePublishPanel
				title={ __( 'Featured Image', 'amp' ) }
				initialOpen="true"
			>
				<Notice
					status={ status }
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
} )( PrePublishPanel );
