/**
 * Internal dependencies
 */
import { STORY_PAGE_INNER_HEIGHT, STORY_PAGE_INNER_WIDTH } from '../constants';

/**
 * Get percentage of a distance compared to the full width / height of the page.
 *
 * @param {string} axis       X or Y axis.
 * @param {number} pixelValue Value in pixels.
 * @param {number} baseValue  Value to compare against to get percentage from.
 *
 * @return {number} Value in percentage.
 */
const getPercentageFromPixels = ( axis, pixelValue, baseValue = 0 ) => {
	if ( ! baseValue ) {
		if ( 'x' === axis ) {
			baseValue = STORY_PAGE_INNER_WIDTH;
		} else if ( 'y' === axis ) {
			baseValue = STORY_PAGE_INNER_HEIGHT;
		} else {
			return 0;
		}
	}
	return Number( ( ( pixelValue / baseValue ) * 100 ).toFixed( 2 ) );
};

export default getPercentageFromPixels;
