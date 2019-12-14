/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import edit from './edit';

export const name = 'amp/amp-latest-stories';

export const settings = {
	title: __( 'Latest Stories', 'amp' ),
	description: __( 'Display your most recent stories.', 'amp' ),
	icon: 'list-view',
	category: 'widgets',
	keywords: [
		__( 'recent stories', 'amp' ),
		__( 'AMP Stories', 'amp' ),
	],

	supports: {
		html: false,
	},

	edit,

	/**
	 * Rendered in PHP as a dynamic block.
	 *
	 * @return {null} Renders in PHP.
	 */
	save() {
		return null;
	},
};
