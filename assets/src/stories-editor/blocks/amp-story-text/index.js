/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createBlock, getBlockAttributes } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import edit from './edit';
import save from './save';
import deprecated from './deprecated';

export const name = 'amp/amp-story-text';

const supports = {
	anchor: true,
	reusable: true,
	className: true,
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
};

export const settings = {
	title: __( 'Text', 'amp' ),

	description: __( 'Add free-form text to your story.', 'amp' ),

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

	save,

	deprecated,

	transforms: {
		from: [
			{
				type: 'raw',
				priority: 20,
				selector: 'p,h1,h2',
				transform: ( node ) => {
					const innerHTML = node.outerHTML;
					const blockAttributes = getBlockAttributes( name, innerHTML );

					/*
					 * When there is nothing that matches the content selector (.amp-text-content),
					 * the pasted content lacks the amp-fit-text wrapper and thus ampFitText is false.
					 */
					if ( ! blockAttributes.content ) {
						blockAttributes.content = node.textContent;
						blockAttributes.tagName = node.nodeName;
						blockAttributes.ampFitText = false;
					}

					return createBlock( name, blockAttributes );
				},
			},
		],
	},
};
