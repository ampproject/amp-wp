/**
 * WordPress dependencies
 */
import { RichText } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import { getClassNameFromBlockAttributes, getStylesFromBlockAttributes } from '../../helpers';


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
	customTextColor: {
		type: 'string',
		default: '#ffffff',
	},
	customBackgroundColor: {
		type: 'string',
		default: '#32373c',
	},
};

const deprecated = [
	{
		attributes: {
			align: {
				type: 'string',
				default: 'center',
			},
			...blockAttributes
		},
		supports: {
			align: true,
			alignWide: false,
		},
		save( { attributes } ) {
			const {
				url,
				text,
			} = attributes;

			const className = getClassNameFromBlockAttributes( { ...attributes, className: 'amp-block-story-cta__link' } );
			const styles = getStylesFromBlockAttributes( attributes );

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
	}
];

export default deprecated;
