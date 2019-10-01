/**
 * Internal dependencies
 */
import { ALLOWED_TOP_LEVEL_BLOCKS } from '../constants';

/**
 * Filter block properties to define the parent block.
 *
 * @param {Object} props      Block properties.
 * @param {string} props.name Block name.
 *
 * @return {Object} Updated properties.
 */
const setBlockParent = ( props ) => {
	const { name } = props;

	if ( ! ALLOWED_TOP_LEVEL_BLOCKS.includes( name ) ) {
		// Only amp/amp-story-page blocks can be on the top level.
		return {
			...props,
			parent: [ 'amp/amp-story-page' ],
		};
	}

	if ( name !== 'amp/amp-story-page' ) {
		// Do not allow inserting any of the blocks if they're not AMP Story blocks.
		return {
			...props,
			parent: [ '' ],
		};
	}

	return props;
};

export default setBlockParent;
