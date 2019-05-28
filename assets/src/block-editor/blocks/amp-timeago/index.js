/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import edit from './edit';
import save from './save';

export const name = 'amp/amp-timeago';

export const settings = {
	title: __( 'AMP Timeago', 'amp' ),
	category: 'common',
	icon: 'backup',
	keywords: [
		__( 'Time difference', 'amp' ),
		__( 'Time ago', 'amp' ),
		__( 'Date', 'amp' ),
	],

	attributes: {
		align: {
			type: 'string',
		},
		cutoff: {
			source: 'attribute',
			selector: 'amp-timeago',
			attribute: 'cutoff',
		},
		dateTime: {
			source: 'attribute',
			selector: 'amp-timeago',
			attribute: 'datetime',
		},
		ampLayout: {
			default: 'fixed-height',
			source: 'attribute',
			selector: 'amp-timeago',
			attribute: 'layout',
		},
		width: {
			source: 'attribute',
			selector: 'amp-timeago',
			attribute: 'width',
		},
		height: {
			default: 20,
			source: 'attribute',
			selector: 'amp-timeago',
			attribute: 'height',
		},
	},

	getEditWrapperProps( attributes ) {
		const { align } = attributes;
		if ( 'left' === align || 'right' === align || 'center' === align ) {
			return { 'data-align': align };
		}
	},

	edit,

	save,
};
