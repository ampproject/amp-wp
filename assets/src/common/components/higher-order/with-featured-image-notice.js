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
import { validateFeaturedImage, getMinimumFeaturedImageDimensions } from '../../helpers';

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

			const errors = validateFeaturedImage( media, getMinimumFeaturedImageDimensions(), false );

			if ( ! errors ) {
				return <PostFeaturedImage { ...props } />;
			}

			return (
				<>
					<Notice
						status="warning"
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
					<PostFeaturedImage { ...props } />
				</>
			);
		};

		withFeaturedImageNotice.propTypes = {
			media: PropTypes.object,
		};

		return withFeaturedImageNotice;
	},
	'withFeaturedImageNotice',
);
