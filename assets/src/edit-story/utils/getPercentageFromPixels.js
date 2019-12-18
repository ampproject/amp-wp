/**
 * Internal dependencies
 */
import { PAGE_HEIGHT, PAGE_WIDTH } from '../constants';

/**
 * Converts pixel value to percentage value based on the editor Page measurements.
 * This is necessary for responsive display in the front-end.
 *
 * @param {number} px Pixel value.
 * @param {string} axis Axis, either `x` or `y`.
 * @return {number} Value in percentage.
 */
function getPercentageFromPixels( px, axis ) {
	if ( 'x' === axis ) {
		return Number( ( ( px / PAGE_WIDTH ) * 100 ).toFixed( 2 ) );
	} else if ( 'y' === axis ) {
		return Number( ( ( px / PAGE_HEIGHT ) * 100 ).toFixed( 2 ) );
	}
	return 0;
}

export default getPercentageFromPixels;
