/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getMetaBlockSettings } from '../../helpers';

export const name = 'amp/amp-story-post-author';

export const settings = {
	title: __( 'Story Author', 'amp' ),
	description: __( 'Displays the name of the story\'s author', 'amp' ),
	category: 'common',
	icon: 'admin-users',
	keywords: [
		__( 'post', 'amp' ),
		__( 'author', 'amp' ),
	],
	...getMetaBlockSettings( {
		tagName: 'span',
		attribute: 'author',
		isEditable: false,
	} ),
};
