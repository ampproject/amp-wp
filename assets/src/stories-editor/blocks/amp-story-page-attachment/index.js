/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import edit from './edit';

const schema = {
	postId: {
		type: 'number',
	},
	postType: {
		type: 'string',
		default: 'post',
	},
	title: {
		type: 'string',
	},
	openText: {
		type: 'string',
		default: __( 'Swipe Up', 'amp' ),
	},
	wrapperStyle: {
		default: {},
	},
	attachmentClass: {
		type: 'string',
		default: 'amp-page-attachment-content',
	},
};

export const name = 'amp/amp-story-page-attachment';

export const settings = {
	title: __( 'Page Attachment', 'amp' ),

	description: __( 'Attach additional content to a story page.', 'amp' ),

	icon: 'media-document',

	category: 'layout',

	keywords: [
		__( 'attachment', 'amp' ),
		__( 'content', 'amp' ),
		__( 'embed', 'amp' ),
	],

	attributes: schema,

	edit,

	save() {
		// Dynamic content, handled by PHP.
		return null;
	},
};
