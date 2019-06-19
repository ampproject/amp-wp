/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getMetaBlockSettings } from '../../helpers';
import './edit.css';

export const name = 'amp/amp-story-post-title';

export const settings = {
	title: __( 'Story Title', 'amp' ),
	description: __( 'Display the story title. Modify by changing the overall title of the document.', 'amp' ),
	category: 'common',
	icon: <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5 4v3h5.5v12h3V7H19V4z" /><path fill="none" d="M0 0h24v24H0V0z" /></svg>,
	keywords: [
		__( 'post', 'amp' ),
		__( 'title', 'amp' ),
	],
	...getMetaBlockSettings( {
		tagName: 'h1',
		attribute: 'title',
		placeholder: __( 'Add story title…', 'amp' ),
		isEditable: true,
	} ),
};
