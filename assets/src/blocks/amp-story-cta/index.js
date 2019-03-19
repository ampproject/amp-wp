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
} from '@wordpress/editor';

/**
 * Internal dependencies
 */
import edit from './edit';
import { registerBlockType } from '@wordpress/blocks';

const blockAttributes = {
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
	positionTop: {
		type: 'number',
		default: 80,
	},
	positionLeft: {
		type: 'number',
		default: 0,
	},
	width: {
		type: 'number',
		default: 100,
	},
	height: {
		type: 'number',
		default: 20,
	},
};

export const name = 'amp/amp-story-cta';

export const settings = {
	title: __( 'Call to Action', 'amp' ),

	description: __( 'Prompt visitors to take action with a button-style link.', 'amp' ),

	icon: <SVG viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><Path fill="none" d="M0 0h24v24H0V0z" /><G><Path d="M19 6H5c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 10H5V8h14v8z" /></G></SVG>,

	category: 'layout',

	keywords: [ __( 'call to action', 'amp' ), __( 'cta', 'amp' ), __( 'button', 'amp' ) ],

	attributes: blockAttributes,

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
		} = attributes;

		const textClass = getColorClassName( 'color', textColor );
		const backgroundClass = getColorClassName( 'background-color', backgroundColor );

		const buttonClasses = classnames( 'wp-block-button__link', {
			'has-text-color': textColor || customTextColor,
			[ textClass ]: textClass,
			'has-background': backgroundColor || customBackgroundColor,
			[ backgroundClass ]: backgroundClass,
		} );

		const buttonStyle = {
			backgroundColor: backgroundClass ? undefined : customBackgroundColor,
			color: textClass ? undefined : customTextColor,
		};

		return (
			<amp-story-cta-layer>
				<RichText.Content
					tagName="a"
					className={ buttonClasses }
					href={ url }
					style={ buttonStyle }
					value={ text }
				/>
			</amp-story-cta-layer>
		);
	},
};

registerBlockType( name, settings );
