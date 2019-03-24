/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getPrePublishNotice, hasMinimumStoryPosterDimensions } from './';

export default getPrePublishNotice(
	hasMinimumStoryPosterDimensions,
	__( 'The featured image must have minimum dimensions of 696px x 928px, 928px x 696px, or 928px x 928px', 'amp' )
);
