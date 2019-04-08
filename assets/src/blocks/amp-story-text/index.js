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
	getFontSize,
	getColorObjectByAttributeValues,
} from '@wordpress/block-editor';
import { select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import edit from './edit';
import { getPercentageFromPixels, getRgbaFromHex } from '../../helpers';
import { STORY_PAGE_INNER_WIDTH } from '../../constants';

export const name = 'amp/amp-story-text';

const supports = {
	className: false,
	anchor: true,
	reusable: true,
};

const schema = {
	placeholder: {
		type: 'string',
	},
	content: {
		type: 'string',
		source: 'html',
		selector: '.amp-text-content',
		default: '',
	},
	type: {
		type: 'string',
		default: 'auto',
	},
	tagName: {
		type: 'string',
		default: 'p',
	},
	align: {
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
	ampFitText: {
		type: 'boolean',
		default: true,
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
	opacity: {
		default: 100,
		type: 'number',
	},
	height: {
		default: 50,
		type: 'number',
	},
	width: {
		default: 250,
		type: 'number',
	},
};

export const settings = {
	title: __( 'Text', 'amp' ),

	description: __( 'Add free-form text to your story', 'amp' ),

	icon: <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M11 5v7H9.5C7.6 12 6 10.4 6 8.5S7.6 5 9.5 5H11m8-2H9.5C6.5 3 4 5.5 4 8.5S6.5 14 9.5 14H11v7h2V5h2v16h2V5h2V3z" /></svg>,

	category: 'common',

	keywords: [
		__( 'title', 'amp' ),
		__( 'heading', 'amp' ),
		__( 'paragraph', 'amp' ),
	],

	supports,

	attributes: schema,

	edit,

	save: ( { attributes } ) => {
		const {
			content,
			align,
			fontSize,
			customFontSize,
			ampFitText,
			autoFontSize,
			backgroundColor,
			textColor,
			customBackgroundColor,
			customTextColor,
			width,
			height,
			tagName,
			opacity,
		} = attributes;

		const textClass = getColorClassName( 'color', textColor );
		const backgroundClass = getColorClassName( 'background-color', backgroundColor );

		const hasOpacity = opacity && opacity < 100;

		const className = classnames( {
			'amp-text-content': ! ampFitText,
			'has-text-color': textColor || customTextColor,
			'has-background': backgroundColor || customBackgroundColor,
			[ textClass ]: textClass,
			[ backgroundClass ]: ! hasOpacity ? backgroundClass : undefined,
		} );

		const { colors, fontSizes } = select( 'core/block-editor' ).getSettings();

		/*
		 * Calculate fontsize using vw to make it responsive.
		 *
		 * Get the font size in px based on the slug with fallback to customFontSize.
		 */
		const userFontSize = fontSize ? getFontSize( fontSizes, fontSize, customFontSize ).size : customFontSize;
		const fontSizeResponsive = ( ( userFontSize / STORY_PAGE_INNER_WIDTH ) * 100 ).toFixed( 2 ) + 'vw';

		let appliedBackgroundColor;

		// If we need to assign opacity.
		if ( hasOpacity && ( backgroundColor || customBackgroundColor ) ) {
			const hexColor = getColorObjectByAttributeValues( colors, backgroundColor, customBackgroundColor );

			if ( hexColor ) {
				const [ r, g, b, a ] = getRgbaFromHex( hexColor.color, opacity );

				appliedBackgroundColor = `rgba( ${ r }, ${ g }, ${ b }, ${ a })`;
			}
		} else if ( ! backgroundClass ) {
			appliedBackgroundColor = customBackgroundColor;
		}

		const styles = {
			backgroundColor: appliedBackgroundColor,
			color: textClass ? undefined : customTextColor,
			fontSize: ampFitText ? autoFontSize : fontSizeResponsive,
			width: `${ getPercentageFromPixels( 'x', width ) }%`,
			height: `${ getPercentageFromPixels( 'y', height ) }%`,
			textAlign: align,
		};

		if ( ! ampFitText ) {
			return (
				<RichText.Content
					tagName={ tagName }
					style={ styles }
					className={ className }
					value={ content }
				/>
			);
		}

		const ContentTag = tagName;

		return (
			<ContentTag
				style={ styles }
				className={ className }>
				<amp-fit-text layout="fill" className="amp-text-content">{ content }</amp-fit-text>
			</ContentTag>
		);
	},
};
