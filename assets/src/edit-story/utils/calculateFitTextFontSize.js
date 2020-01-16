/**
 * Internal dependencies
 */
import { MIN_FONT_SIZE, MAX_FONT_SIZE } from '../constants';

/**
 * Calculates font size that fits to the text element based on the element's size.
 * Replicates amp-fit-text's logic in the editor.
 *
 * @see https://github.com/ampproject/amphtml/blob/e7a1b3ff97645ec0ec482192205134bd0735943c/extensions/amp-fit-text/0.1/amp-fit-text.js
 *
 * @param {Object} measurer       HTML element.
 * @param {number} expectedHeight Maximum height.
 * @param {number} expectedWidth  Maximum width.
 *
 * @return {number|boolean} Calculated font size. False if calculation wasn't possible.
 */
export const calculateFitTextFontSize = ( measurer, expectedHeight, expectedWidth ) => {
	let maxFontSize = MAX_FONT_SIZE;
	let minFontSize = MIN_FONT_SIZE;

	// Return false if calculation is not possible due to width and height missing, e.g. in disabled preview.
	if ( ! measurer.offsetHeight || ! measurer.offsetWidth ) {
		return false;
	}
	// @todo Alternatively we could assign a class add CSS for that class.
	// @todo Use isMeasuring state perhaps instead which could influence the CSS directly in the Text block's display.
	// Add necessary styles for measuring:
	measurer.style.display = 'inline-block';
	measurer.style.height = 'initial';
	measurer.style.width = 'initial';
	measurer.style.position = 'absolute';

	maxFontSize++;

	// Binomial search for the best font size.
	while ( maxFontSize - minFontSize > 1 ) {
		const mid = Math.floor( ( minFontSize + maxFontSize ) / 2 );
		measurer.style.fontSize = mid + 'px';
		const currentHeight = measurer.offsetHeight;
		const currentWidth = measurer.offsetWidth;
		if ( currentHeight > expectedHeight || currentWidth > expectedWidth ) {
			maxFontSize = mid;
		} else {
			minFontSize = mid;
		}
	}

	// Let's restore the correct font size, too.
	measurer.style.fontSize = minFontSize + 'px';
	// Restore style values. @todo Get style from the original element instead of just emptying.
	measurer.style.display = '';
	measurer.style.height = '';
	measurer.style.width = '';
	measurer.style.position = '';

	return minFontSize;
};
