/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getPrePublishNotice } from './';
import { hasMinimumStoryPosterDimensions } from '../helpers';

export default getPrePublishNotice(
	hasMinimumStoryPosterDimensions,
	__( 'The featured image must have minimum dimensions of 696px x 928px', 'amp' )
);
