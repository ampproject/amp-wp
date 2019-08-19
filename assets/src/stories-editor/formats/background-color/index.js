/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import FormatEdit from './edit';

export const priority = 20;

export const name = 'amp/background-color';

export const settings = {
	title: __( 'Inline Background Color', 'amp' ),
	tagName: 'span',
	className: 'amp-background-color',
	attributes: {
		style: 'style',
	},
	edit: FormatEdit,
};

