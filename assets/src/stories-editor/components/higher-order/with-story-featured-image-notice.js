/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { Notice } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { getMinimumStoryPosterDimensions } from '../../helpers';
import { validateFeaturedImage } from '../../../common/helpers';

/**
 * Higher-order component that is used for filtering the PostFeaturedImage component for AMP stories.
 *
 * Used to display notices in case the image does not meet minimum requirements.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	( PostFeaturedImage ) => {
		const withStoryFeaturedImageNotice = ( props ) => {
			const { media } = props;

			const errors = validateFeaturedImage( media, getMinimumStoryPosterDimensions(), true );

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

		withStoryFeaturedImageNotice.propTypes = {
			media: PropTypes.object,
		};

		return withStoryFeaturedImageNotice;
	},
	'withStoryFeaturedImageNotice'
);
