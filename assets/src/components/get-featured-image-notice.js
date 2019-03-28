/**
 * WordPress dependencies
 */
import { Notice } from '@wordpress/components';
import { Fragment } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { getFeaturedImageMessage } from '../helpers';

/**
 * Conditionally displays a notice above the post's 'Featured Image' component.
 *
 * If there is no featured image, this displays a notice.
 * This displays in the sidebar, in the 'Document' panel.
 *
 * @param {Function} validateImageSize A function that determines whether the media size is correct.
 * @param {string} invalidSizeMessage The message to display in the Notice if the size is wrong.
 * @return {Function} The PostFeaturedImage component, with in a Notice if there's no featured image.
 */
export default ( validateImageSize, invalidSizeMessage ) => {
	return ( PostFeaturedImage ) => {
		return ( props ) => {
			const featuredImageMessage = getFeaturedImageMessage( validateImageSize, invalidSizeMessage );
			const postFeaturedImage = (
				<PostFeaturedImage { ...props } />
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
