// Todo: Make these customizable?
const H1_FONT_SIZE = 40;
const H2_FONT_SIZE = 24;

/**
 * Determines the HTML tag name that should be used given on the block's attributes.
 *
 * @todo Use @wordpress/wordcount package to add autosmartness based on text length.
 *
 * @param {Object} attributes Block attributes.
 * @return {string} HTML tag name. Either p, h1, or h2.
 */
export default function( attributes ) {
	const { type, fontSize, customFontSize } = attributes;

	if ( -1 !== [ 'h1', 'h2', 'p' ].indexOf( type ) ) {
		return type;
	}

	if ( 'huge' === fontSize || ( customFontSize && customFontSize > H1_FONT_SIZE ) ) {
		return 'h1';
	}

	if ( 'large' === fontSize || ( customFontSize && customFontSize > H2_FONT_SIZE ) ) {
		return 'h2';
	}

	return 'p';
}
