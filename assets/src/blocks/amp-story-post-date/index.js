/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getMetaBlockSettings } from '../../helpers';

export const name = 'amp/amp-story-post-date';

export const settings = {
	title: __( 'Story Date', 'amp' ),
	description: __( 'Displays the date the story has been published on', 'amp' ),
	category: 'common',
	icon: 'clock',
	keywords: [
		__( 'post', 'amp' ),
		__( 'date', 'amp' ),
	],
	...getMetaBlockSettings( {
		tagName: 'span',
		attribute: 'date',
		isEditable: false,
	} ),
};
