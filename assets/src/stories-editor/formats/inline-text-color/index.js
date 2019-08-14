/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import FormatEdit from './edit';

export const name = 'amp/text-color';

export const settings = {
	title: __( 'Inline Text Color', 'amp' ),
	tagName: 'span',
	className: 'amp-text-color',
	attributes: {
		style: 'style',
	},
	edit: FormatEdit,
};
