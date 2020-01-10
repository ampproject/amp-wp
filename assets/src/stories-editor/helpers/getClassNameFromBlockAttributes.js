/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { getColorClassName } from '@wordpress/block-editor';

/**
 * Determines a block's HTML class name based on its attributes.
 *
 * @param {Object}   attributes                       Block attributes.
 * @param {string[]} attributes.className             List of pre-existing class names for the block.
 * @param {boolean}  attributes.ampFitText            Whether amp-fit-text should be used or not.
 * @param {?string}  attributes.backgroundColor       A string containing the background color slug.
 * @param {?string}  attributes.textColor             A string containing the color slug.
 * @param {string}   attributes.customBackgroundColor A string containing the custom background color value.
 * @param {string}   attributes.customTextColor       A string containing the custom color value.
 * @param {?number}  attributes.opacity               Opacity.
 *
 * @return {string} The block's HTML class name.
 */
const getClassNameFromBlockAttributes = ( {
	className,
	ampFitText,
	backgroundColor,
	textColor,
	customBackgroundColor,
	customTextColor,
	opacity,
} ) => {
	const textClass = getColorClassName( 'color', textColor );
	const backgroundClass = getColorClassName( 'background-color', backgroundColor );

	const hasOpacity = opacity && opacity < 100;

	return classnames( className, {
		'amp-text-content': ! ampFitText,
		'has-text-color': textColor || customTextColor,
		'has-background': backgroundColor || customBackgroundColor,
		[ textClass ]: textClass,
		[ backgroundClass ]: ! hasOpacity ? backgroundClass : undefined,
	} );
};

export default getClassNameFromBlockAttributes;
