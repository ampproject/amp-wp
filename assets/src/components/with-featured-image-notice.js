/**
 * WordPress dependencies
 */
import {__, sprintf } from '@wordpress/i18n';
import { createHigherOrderComponent } from '@wordpress/compose';
import { Notice } from '@wordpress/components';
import { Fragment } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { hasMinimumFeaturedImageWidth, getMinimumFeaturedImageDimensions } from '../helpers';

/**
 * Higher-order component that is used for filtering the PostFeaturedImage component.
 *
 * Used to display notices in case the image does not meet minimum requirements.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	( PostFeaturedImage ) => {
		return ( props ) => {
			const { media } = props;

			if ( media && hasMinimumFeaturedImageWidth( media.media_details ) ) {
				return <PostFeaturedImage { ...props } />;
			}

			const minDimensions = getMinimumFeaturedImageDimensions();

			const message = ! media ?
				__( 'Selecting a featured image is recommended for an optimal user experience.', 'amp' ) :
				/* translators: %1$s: Minimum width, %2$s: Minimum height. */
				sprintf( __( 'The featured image should have a width of at least %1$s by %2$s pixels.', 'amp' ), minDimensions.width, minDimensions.height );

			return (
				<Fragment>
					<Notice
						status="notice"
						isDismissible={ false }
					>
						<span>
							{ message }
						</span>
					</Notice>
					<PostFeaturedImage { ...props } />
				</Fragment>
			);
		};
	},
	'withFeaturedImageNotice'
);
