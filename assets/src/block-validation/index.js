/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { INITIAL_STATE } from './store';
import { MoreMenuIcon } from './icon';
import './toolbar-button';
import { AMPBlockValidation } from './amp-block-validation';

export const PLUGIN_NAME = 'amp-block-validation';
export const SIDEBAR_NAME = 'amp-editor-sidebar';
export const PLUGIN_TITLE = __( 'AMP for WordPress', 'amp' );

registerPlugin(
	PLUGIN_NAME,
	{
		icon: MoreMenuIcon,
		render: ( props ) => <AMPBlockValidation initialState={ INITIAL_STATE } { ...props } />,
	},
);
