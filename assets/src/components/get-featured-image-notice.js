/**
 * WordPress dependencies
 */
import { Notice } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import getFeaturedImageMessage from './get-featured-image-message';

/**
 * Conditionally displays a notice above the post's 'Featured Image' component.
 *
 * If there is no featured image, this displays a notice.
 * This displays in the sidebar, in the 'Document' panel.
 *
 * @param {Function} PostFeaturedImage The featured image component, appearing in the sidebar.
 * @return {Function} The PostFeaturedImage component, wrapped in a Notice if there's no featured image.
 */
export default ( validateImageSize, invalidSizeMessage ) => {
	return ( PostFeaturedImage ) => {
		return ( props ) => {
			const featuredImageMessage = getFeaturedImageMessage( validateImageSize, invalidSizeMessage );
			const postFeaturedImage = (
				<PostFeaturedImage {...props} />
			);

			if ( ! featuredImageMessage ) {
				return postFeaturedImage;
			}

			return (
				<Fragment>
					<Notice status="warning">
						<span>
							{ featuredImageMessage }
						</span>
					</Notice>
					{ postFeaturedImage }
				</Fragment>
			);
		};
	};
};
