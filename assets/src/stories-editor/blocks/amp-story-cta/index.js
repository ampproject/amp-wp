/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	RichText,
	getColorClassName,
	getFontSizeClass,
} from '@wordpress/editor';

/**
 * Internal dependencies
 */
import edit from './edit';
import blockIcon from '../../../../images/call-to-action.svg';

const schema = {
	url: {
		type: 'string',
		source: 'attribute',
		selector: 'a',
		attribute: 'href',
	},
	text: {
		type: 'string',
		source: 'html',
		selector: 'a',
	},
};

export const name = 'amp/amp-story-cta';

export const settings = {
	title: __( 'Call to Action', 'amp' ),

	description: __( 'Prompt visitors to take action with a button-style link.', 'amp' ),

	icon: blockIcon,

	category: 'layout',

	keywords: [ __( 'call to action', 'amp' ), __( 'cta', 'amp' ), __( 'button', 'amp' ) ],

	attributes: schema,

	supports: {
		align: true,
		alignWide: false,
	},

	edit,

	save( { attributes } ) {
		const {
			url,
			text,
			backgroundColor,
			textColor,
			customBackgroundColor,
			customTextColor,
			fontSize,
			customFontSize,
		} = attributes;

		const textClass = getColorClassName( 'color', textColor );
		const backgroundClass = getColorClassName( 'background-color', backgroundColor );
		const fontSizeClass = getFontSizeClass( fontSize );

		const className = classnames( 'amp-block-story-cta__link', {
			'has-text-color': textColor || customTextColor,
			[ textClass ]: textClass,
			'has-background': backgroundColor || customBackgroundColor,
			[ backgroundClass ]: backgroundClass,
			[ fontSizeClass ]: fontSizeClass,
		} );

		const styles = {
			backgroundColor: backgroundClass ? undefined : customBackgroundColor,
			color: textClass ? undefined : customTextColor,
			fontSize: fontSizeClass ? undefined : customFontSize,
		};

		return (
			<amp-story-cta-layer>
				<RichText.Content
					tagName="a"
					className={ className }
					href={ url }
					style={ styles }
					value={ text }
				/>
			</amp-story-cta-layer>
		);
	},
};
