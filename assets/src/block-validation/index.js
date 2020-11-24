/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';

/**
 * Internal dependencies
 */
import { INITIAL_STATE, createStore } from './store';
import { MoreMenuIcon } from './icon';
import { AMPBlockValidation } from './amp-block-validation';
import { filterBlocksEdit } from './toolbar-button';

createStore( INITIAL_STATE );

export const PLUGIN_NAME = 'amp-block-validation';
export const SIDEBAR_NAME = 'amp-editor-sidebar';
export const PLUGIN_TITLE = __( 'AMP for WordPress', 'amp' );

registerPlugin(
	PLUGIN_NAME,
	{
		icon: MoreMenuIcon,
		render: AMPBlockValidation,
	},
);

addFilter( 'editor.BlockEdit', 'ampBlockValidation/filterEdit', filterBlocksEdit, -99 );
