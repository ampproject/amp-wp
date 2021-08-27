/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { isFunction } from 'lodash';

/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { Notice } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { __ } from '@wordpress/i18n';
import { validateFeaturedImage, getMinimumFeaturedImageDimensions } from '../../helpers';

/**
 * Create notice UI for featured image component.
 *
 * @param {string[]} messages Notices.
 * @param {string}   status   Status type of notice.
 * @return {JSX.Element} Notice component.
 */
const createNoticeUI = ( messages, status ) => {
	return (
		<Notice
			status={ status }
			isDismissible={ false }
		>
			{ messages.map( ( message, index ) => {
				return (
					<p key={ `message-${ index }` }>
						{ message }
					</p>
				);
			} ) }
		</Notice>
	);
};

/**
 * Higher-order component that is used for filtering the PostFeaturedImage component.
 *
 * Used to display notices in case the image does not meet minimum requirements.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	( PostFeaturedImage ) => {
		if ( ! isFunction( PostFeaturedImage ) ) {
			return PostFeaturedImage;
		}

		const withFeaturedImageNotice = ( props ) => {
			const { media } = props;
			let noticeUI;

			if ( ! media ) {
				const message = __( 'Selecting a featured image is recommended for an optimal user experience.', 'amp' );
				noticeUI = createNoticeUI( [ message ], 'notice' );
			} else {
				const errorMessages = validateFeaturedImage( media, getMinimumFeaturedImageDimensions() );
				noticeUI = errorMessages ? createNoticeUI( errorMessages, 'warning' ) : null;
			}

			return (
				<PostFeaturedImage { ...props } noticeUI={ noticeUI } />
			);
		};

		withFeaturedImageNotice.propTypes = {
			media: PropTypes.object,
		};

		return withFeaturedImageNotice;
	},
	'withFeaturedImageNotice',
);
