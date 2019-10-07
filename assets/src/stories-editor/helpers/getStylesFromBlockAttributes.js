/**
 * WordPress dependencies
 */
import { getColorClassName, getColorObjectByAttributeValues, getFontSize } from '@wordpress/block-editor';
import { select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORY_PAGE_INNER_WIDTH } from '../constants';
import { getBackgroundColorWithOpacity } from '../../common/helpers';

/**
 * Determines a block's inline style based on its attributes.
 *
 * @param {Object}  attributes                       Block attributes.
 * @param {string}  attributes.align                 Block alignment.
 * @param {?string} attributes.fontSize              Font size slug.
 * @param {?number} attributes.customFontSize        Custom font size in pixels.
 * @param {boolean} attributes.ampFitText            Whether amp-fit-text should be used or not.
 * @param {?string} attributes.backgroundColor       A string containing the background color slug.
 * @param {?string} attributes.textColor             A string containing the color slug.
 * @param {string}  attributes.customBackgroundColor A string containing the custom background color value.
 * @param {string}  attributes.customTextColor       A string containing the custom color value.
 * @param {?number} attributes.opacity               Opacity.
 *
 * @return {Object} Block inline style.
 */
const getStylesFromBlockAttributes = ( {
	align,
	fontSize,
	customFontSize,
	ampFitText,
	backgroundColor,
	textColor,
	customBackgroundColor,
	customTextColor,
	opacity,
} ) => {
	const textClass = getColorClassName( 'color', textColor );

	const { colors, fontSizes } = select( 'core/block-editor' ).getSettings();

	/*
     * Calculate font size using vw to make it responsive.
     *
     * Get the font size in px based on the slug with fallback to customFontSize.
     */
	const userFontSize = fontSize ? getFontSize( fontSizes, fontSize, customFontSize ).size : customFontSize;
	const fontSizeResponsive = userFontSize && ( ( userFontSize / STORY_PAGE_INNER_WIDTH ) * 100 ).toFixed( 2 ) + 'vw';

	const appliedBackgroundColor = getBackgroundColorWithOpacity( colors, getColorObjectByAttributeValues( colors, backgroundColor, customBackgroundColor ), customBackgroundColor, opacity );

	return {
		backgroundColor: appliedBackgroundColor,
		color: textClass ? undefined : customTextColor,
		fontSize: ! ampFitText ? fontSizeResponsive : undefined,
		textAlign: align ? align : undefined,
	};
};

export default getStylesFromBlockAttributes;
