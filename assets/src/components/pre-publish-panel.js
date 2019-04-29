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
import { validateFeaturedImage } from '../stories-editor/helpers';

/**
 * Conditionally adds a notice to the pre-publish panel for the featured image.
 *
 * @return {Function} Either a plain pre-publish panel, or the panel with a featured image notice.
 */
const PrePublishPanel = ( { featuredMedia, dimensions, required } ) => {
	const errors = validateFeaturedImage( featuredMedia, dimensions, required );

	if ( ! errors ) {
		return null;
	}

	return (
		<Fragment>
			<PluginPrePublishPanel
				title={ __( 'Featured Image', 'amp' ) }
				initialOpen="true"
			>
				<Notice
					status={ required ? 'warning' : 'notice' }
					isDismissible={ false }
				>
					{ errors.map( ( errorMessage, index ) => {
						return (
							<p key={ `error-${ index }` }>
								{ errorMessage }
							</p>
						);
					} ) }
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
		featuredMedia: featuredMedia ? select( 'core' ).getMedia( featuredMedia ) : null,
	};
} )( PrePublishPanel );
