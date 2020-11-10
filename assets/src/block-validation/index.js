/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY } from './store';
import { ToolbarIcon, MoreMenuIcon } from './icon';
import { Sidebar } from './sidebar';
import { InvalidBlockOutline } from './invalid-block-outline';
import { BlockValidationStateUpdater } from './block-validation-state-updater';
import './toolbar-button';

export const PLUGIN_NAME = 'amp-block-validation';
export const SIDEBAR_NAME = 'amp-editor-sidebar';
const title = __( 'AMP for WordPress', 'amp' );

/**
 * Provides a dedicated sidebar for the plugin, with toggle buttons in the editor toolbar and more menu.
 */
function AMPBlockValidation() {
	const { broken, errorCount } = useSelect( ( select ) => ( {
		broken: select( BLOCK_VALIDATION_STORE_KEY ).getAMPBroken(),
		errorCount: select( BLOCK_VALIDATION_STORE_KEY ).getUnreviewedValidationErrors()?.length || 0,
	} ) );

	return (
		<>
			<PluginSidebarMoreMenuItem
				icon={ <MoreMenuIcon /> }
				target={ SIDEBAR_NAME }
			>
				{ title }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar
				className={ `${ PLUGIN_NAME }-sidebar` }
				icon={ (
					<ToolbarIcon count={ errorCount } broken={ broken } />
				) }
				name={ SIDEBAR_NAME }
				title={ title }
			>

				<Sidebar />
				<InvalidBlockOutline />
			</PluginSidebar>
			<BlockValidationStateUpdater />
		</>
	);
}

registerPlugin( PLUGIN_NAME, { icon: MoreMenuIcon, render: AMPBlockValidation } );

