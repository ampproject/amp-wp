/**
 * Updates Text element's width and height if it's being resized from edges or there are font changes.
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
		// @todo This is not working fully as expected, it might also need adjusting width if the scrollHeight is over
		if ( element.scrollWidth > width ) {
			width = element.scrollWidth;
		}
	} else if ( element.scrollHeight > height || element.scrollWidth > width ) {
		// If there's no fixed side, let's update both.
		height = element.scrollHeight;
		width = element.scrollWidth;
	}
	return { width, height };
}

export default getAdjustedElementDimensions;
