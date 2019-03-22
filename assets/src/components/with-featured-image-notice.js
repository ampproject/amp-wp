/**
 * WordPress dependencies
 */
import { Notice } from '@wordpress/components';
import { withSelect } from '@wordpress/data';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Conditionally displays a notice above the post's 'Featured Image' component.
 *
 * If there is no featured image, this displays a notice.
 * This displays in the sidebar, in the 'Document' panel.
 *
 * @param {Function} PostFeaturedImage The featured image component, appearing in the sidebar.
 * @return {Function} The PostFeaturedImage component, wrapped in a Notice if there's no featured image.
 */
export default ( PostFeaturedImage ) => {
	return withSelect( ( select ) => {
		return {
			currentPost: select( 'core/editor' ).getCurrentPost(),
			editedFeaturedMedia: select( 'core/editor' ).getEditedPostAttribute( 'featured_media' ),
		};
	} )( ( ownProps ) => {
		const { currentPost, editedFeaturedMedia } = ownProps,
			hasFeaturedMedia = currentPost.featured_media || editedFeaturedMedia;

		function AmpNoticeBlockEdit( props ) {
			const postFeaturedImage = (
				<PostFeaturedImage { ...props } />
			);

			if ( hasFeaturedMedia ) {
				return postFeaturedImage;
			}

			return (
				<Fragment>
					<Notice status="warning">
						<span>
							{ __( 'Featured image is used as poster-portrait-src and is mandatory', 'amp' ) }
						</span>
					</Notice>
					{ postFeaturedImage }
				</Fragment>
			);
		}
		return AmpNoticeBlockEdit( ownProps );
	} );
};
