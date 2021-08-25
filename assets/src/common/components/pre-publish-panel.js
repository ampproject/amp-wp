/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Notice } from '@wordpress/components';
import { PostFeaturedImage } from '@wordpress/editor';
import { PluginPrePublishPanel } from '@wordpress/edit-post';
import { withSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { validateFeaturedImage } from '../helpers';

/**
 * Conditionally adds a notice to the pre-publish panel for the featured image.
 *
 * @param {Object}  props               Component props.
 * @param {Object}  props.featuredMedia Media object.
 * @param {Array}   props.dimensions    Required image dimensions.
 * @param {boolean} props.required      Whether selecting a featured image is required.
 * @return {Function} Either a plain pre-publish panel, or the panel with a featured image notice.
 */
const PrePublishPanel = ( { featuredMedia, dimensions, required } ) => {
	const errors = validateFeaturedImage( featuredMedia, dimensions, required );

	if ( ! errors ) {
		return null;
	}

	return (
		<PluginPrePublishPanel
			title={ __( 'Featured Image', 'amp' ) }
			initialOpen="true"
		>
			<PostFeaturedImage
				noticeUI={
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
				}
			/>
		</PluginPrePublishPanel>
	);
};

PrePublishPanel.propTypes = {
	featuredMedia: PropTypes.object,
	dimensions: PropTypes.shape( {
		width: PropTypes.number.isRequired,
		height: PropTypes.number.isRequired,
	} ),
	required: PropTypes.bool,
};

export default withSelect( ( select ) => {
	const currentPost = select( 'core/editor' ).getCurrentPost();
	const editedFeaturedMedia = select( 'core/editor' ).getEditedPostAttribute( 'featured_media' );
	const featuredMedia = currentPost.featured_media || editedFeaturedMedia;

	return {
		featuredMedia: featuredMedia ? select( 'core' ).getMedia( featuredMedia ) : null,
	};
} )( PrePublishPanel );
