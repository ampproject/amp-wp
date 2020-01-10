/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getMetaBlockSettings } from '../../helpers';
import './edit.css';

export const name = 'amp/amp-story-post-date';

export const settings = {
	title: __( 'Story Date', 'amp' ),
	description: __( 'Display the publish date of the story. Modify by changing the date in the document settings.', 'amp' ),
	category: 'common',
	icon: 'clock',
	keywords: [
		__( 'publish date', 'amp' ),
	],
	...getMetaBlockSettings( {
		tagName: 'div',
		attribute: 'date',
		isEditable: false,
	} ),
};
