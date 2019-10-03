/**
 * Adds either background color or gradient to style depending on the settings.
 *
 * @param {Object} overlayStyle     Original style.
 * @param {Array}  backgroundColors Array of color settings.
 *
 * @return {Object} Adjusted style.
 */
const addBackgroundColorToOverlay = ( overlayStyle, backgroundColors ) => {
	const validBackgroundColors = backgroundColors.filter( Boolean );

	if ( ! validBackgroundColors ) {
		return overlayStyle;
	}

	if ( 1 === validBackgroundColors.length ) {
		overlayStyle.backgroundColor = validBackgroundColors[ 0 ].color;
	} else {
		const gradientList = validBackgroundColors.map( ( { color } ) => {
			return color || 'transparent';
		} ).join( ', ' );

		overlayStyle.backgroundImage = `linear-gradient(to bottom, ${ gradientList })`;
	}
	return overlayStyle;
};

export default addBackgroundColorToOverlay;
