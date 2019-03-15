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
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import edit from './edit';
import getTagName from './get-tag-name';

export const name = 'amp/amp-story-text';

const supports = {
	className: false,
	anchor: true,
};

const schema = {
	placeholder: {
		type: 'string',
	},
	content: {
		type: 'string',
		source: 'html',
		selector: 'p,h1,h2',
		default: '',
	},
	type: {
		type: 'string',
		default: 'auto',
	},
	fontSize: {
		type: 'string',
	},
	customFontSize: {
		type: 'number',
	},
	ampFontFamily: {
		type: 'string',
	},
	textColor: {
		type: 'string',
	},
	customTextColor: {
		type: 'string',
	},
	backgroundColor: {
		type: 'string',
	},
	customBackgroundColor: {
		type: 'string',
	},
	height: {
		default: 50,
		type: 'number',
	},
	width: {
		default: 100,
		type: 'number',
	},
};

export const settings = {
	title: __( 'Text', 'amp' ),

	description: __( 'Add free-form text to your story', 'amp' ),

	icon: <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5 4v3h5.5v12h3V7H19V4z" /><path fill="none" d="M0 0h24v24H0V0z" /></svg>,

	category: 'common',

	keywords: [
		__( 'title', 'amp' ),
		__( 'heading', 'amp' ),
		__( 'paragraph', 'amp' ),
	],

	supports,

	attributes: schema,

	edit,

	save( { attributes } ) {
		const {
			content,
			fontSize,
			customFontSize,
			backgroundColor,
			textColor,
			customBackgroundColor,
			customTextColor,
			width,
			height,
		} = attributes;

		const textClass = getColorClassName( 'color', textColor );
		const backgroundClass = getColorClassName( 'background-color', backgroundColor );
		const fontSizeClass = getFontSizeClass( fontSize );

		const tagName = getTagName( attributes );

		const className = classnames( {
			'has-text-color': textColor || customTextColor,
			'has-background': backgroundColor || customBackgroundColor,
			[ fontSizeClass ]: fontSizeClass,
			[ textClass ]: textClass,
			[ backgroundClass ]: backgroundClass,
		} );

		const styles = {
			backgroundColor: backgroundClass ? undefined : customBackgroundColor,
			color: textClass ? undefined : customTextColor,
			fontSize: fontSizeClass ? undefined : customFontSize,
			width,
			height,
		};

		return (
			<RichText.Content
				tagName={ tagName }
				style={ styles }
				className={ className }
				value={ content }
			/>
		);
	},
};

registerBlockType( name, settings );
