/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { RichText } from '@wordpress/block-editor';
import { RawHTML } from '@wordpress/element';

/**
 * Internal dependencies
 */
import edit from './edit';
import {
	getClassNameFromBlockAttributes,
	getStylesFromBlockAttributes,
} from '../../helpers';

export const name = 'amp/amp-story-text';

const supports = {
	anchor: true,
	reusable: true,
	className: false,
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
	autoFontSize: {
		type: 'number',
		default: 45,
	},
	ampFitText: {
		type: 'boolean',
		default: true,
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
			ampFitText,
			tagName,
		} = attributes;

		const className = getClassNameFromBlockAttributes( attributes );
		const styles = getStylesFromBlockAttributes( attributes );

		if ( ! ampFitText ) {
			return (
				<RichText.Content
					tagName={ tagName }
					style={ styles }
					className={ className }
					value={ content }
					format="string"
				/>
			);
		}

		const ContentTag = tagName;

		styles.display = 'flex';

		// Uses RawHTML to mimic RichText.Content behavior.
		return (
			<ContentTag
				style={ styles }
				className={ className }>
				<amp-fit-text layout="flex-item" className="amp-text-content"><RawHTML>{ content }</RawHTML></amp-fit-text>
			</ContentTag>
		);
	},
};
