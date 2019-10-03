/**
 * Internal dependencies
 */
import { STORY_PAGE_INNER_HEIGHT, STORY_PAGE_INNER_WIDTH } from '../constants';

/**
 * Get pixel value from percentage, based on a base value to measure against.
 * By default the full width / height of the page.
 *
 * @param {string} axis            X or Y axis.
 * @param {number} percentageValue Value in percent.
 * @param {number} baseValue       Value to compare against to get pixels from.
 *
 * @return {number} Value in percentage.
 */
const getPixelsFromPercentage = ( axis, percentageValue, baseValue = 0 ) => {
	if ( ! baseValue ) {
		if ( 'x' === axis ) {
			baseValue = STORY_PAGE_INNER_WIDTH;
		} else if ( 'y' === axis ) {
			baseValue = STORY_PAGE_INNER_HEIGHT;
		}
	}
	return Math.round( ( percentageValue / 100 ) * baseValue );
};

export default getPixelsFromPercentage;
