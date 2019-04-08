/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { G, Path, SVG } from '@wordpress/components';
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
	backgroundColor: {
		type: 'string',
	},
	textColor: {
		type: 'string',
	},
	customBackgroundColor: {
		type: 'string',
	},
	customTextColor: {
		type: 'string',
	},
	fontSize: {
		type: 'string',
	},
	customFontSize: {
		type: 'number',
	},
	autoFontSize: {
		type: 'number',
	},
	ampFontFamily: {
		type: 'string',
	},
};

export const name = 'amp/amp-story-cta';

export const settings = {
	title: __( 'Call to Action', 'amp' ),

	description: __( 'Prompt visitors to take action with a button-style link.', 'amp' ),

	icon: <SVG viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><Path fill="none" d="M0 0h24v24H0V0z" /><G><Path d="M19 6H5c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 10H5V8h14v8z" /></G></SVG>,

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
