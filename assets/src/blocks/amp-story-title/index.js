/**
 * Much of this block is taken from the Core Heading block.
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { RichText } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import edit from './edit';

export const name = 'amp/amp-story-title';

export const settings = {
	title: __( 'AMP Title', 'amp' ),
	description: __( 'Displays the title of the story', 'amp' ),
	icon: 'list-view',
	category: 'common',
	keywords: [
		__( 'AMP stories', 'amp' ),
	],
	attributes: {
		content: {
			type: 'string',
			source: 'html',
			selector: 'h1,h2,h3,h4,h5,h6',
			default: '',
		},
		level: {
			type: 'number',
			default: 2,
		},
		align: {
			type: 'string',
		},
		placeholder: {
			type: 'string',
		},
	},
	supports: {
		html: false,
	},

	edit,

	/**
	 * Saves the results of the edit component.
	 *
	 * @todo: possibly use PHP to render this instead, as the title could change.
	 * @return {Function} A RichText.Content component.
	 */
	save( { attributes } ) {
		const { align, level, content } = attributes;
		const tagName = 'h' + level;

		return (
			<RichText.Content
				tagName={ tagName }
				style={ { textAlign: align } }
				value={ content }
			/>
		);
	},
};
