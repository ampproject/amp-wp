/**
 * Internal dependencies
 */
import { PAGE_WIDTH } from '../constants';

/**
 * Updates Text element's width and height if it's being resized from edges or there are font changes.
 *
 * @param {Object}  params Params.
 * @param {Object}  params.element Element to measure.
 * @param {string}  params.content Text content.
 * @param {number}  params.width   Current element width.
 * @param {number}  params.height  Current element height.
 * @param {boolean} params.fixedMeasure If one side is locked for changing automatically, can be 'width' or 'height'.
 */
function getAdjustedElementDimensions( { element, content, width, height, fixedMeasure } ) {
	if ( ! element || ! content.length ) {
		return { width, height };
	}
	if ( 'width' === fixedMeasure ) {
		if ( element.scrollHeight > height ) {
			height = element.scrollHeight;
		}
	} else if ( 'height' === fixedMeasure ) {
		if ( element.scrollWidth > width ) {
			width = element.scrollWidth;
		}
		// Width isn't adjusted automatically, so we'll have to do it based on height.
		const calcBuffer = 2;
		if ( element.scrollHeight - height > calcBuffer ) {
			let minWidth = width;
			// Don't allow automatic resizing more than the page's width.
			let maxWidth = PAGE_WIDTH;
			while ( maxWidth - minWidth > 1 ) {
				const mid = Math.floor( ( minWidth + maxWidth ) / 2 );
				element.style.width = mid + 'px';
				if ( element.scrollHeight - height > 2 ) {
					maxWidth = mid;
				} else {
					minWidth = mid;
				}
			}
			// If it still doesn't fit, restore the original width.
			if ( element.scrollHeight - height > calcBuffer ) {
				element.style.width = width + 'px';
			} else {
				// If it fits, return the updated width.
				width = minWidth;
			}
		}
	} else if ( element.scrollHeight > height || element.scrollWidth > width ) {
		// If there's no fixed side, let's update both.
		height = element.scrollHeight;
		width = element.scrollWidth;
	}
	return { width, height };
}

export default getAdjustedElementDimensions;
