/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { __ } from '@wordpress/i18n';
import { addFilter } from '@wordpress/hooks';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { INITIAL_STATE, createStore, BLOCK_VALIDATION_STORE_KEY } from './store';
import { MoreMenuIcon, ToolbarIcon } from './icon';
import { withAMPToolbarButton } from './with-amp-toolbar-button';
import { Sidebar } from './sidebar';
import { InvalidBlockOutline } from './invalid-block-outline';
import { usePostDirtyStateChanges } from './use-post-dirty-state-changes';
import { useValidationErrorStateUpdates } from './use-validation-error-state-updates';

export const PLUGIN_NAME = 'amp-block-validation';
export const SIDEBAR_NAME = 'amp-editor-sidebar';
export const PLUGIN_TITLE = __( 'AMP Validation', 'amp' );

createStore( INITIAL_STATE );

/**
 * Provides a dedicated sidebar for the plugin, with toggle buttons in the editor toolbar and more menu.
 */
function AMPBlockValidation() {
	const { broken, errorCount } = useSelect( ( select ) => ( {
		broken: select( BLOCK_VALIDATION_STORE_KEY ).getAMPCompatibilityBroken(),
		errorCount: select( BLOCK_VALIDATION_STORE_KEY ).getUnreviewedValidationErrors()?.length || 0,
	} ), [] );

	useValidationErrorStateUpdates();
	usePostDirtyStateChanges();

	return (
		<>
			<PluginSidebarMoreMenuItem
				icon={ <MoreMenuIcon /> }
				target={ SIDEBAR_NAME }
			>
				{ PLUGIN_TITLE }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar
				className={ `${ PLUGIN_NAME }-sidebar` }
				icon={ (
					<ToolbarIcon count={ errorCount } broken={ broken } />
				) }
				name={ SIDEBAR_NAME }
				title={ PLUGIN_TITLE }
			>

				<Sidebar />
				<InvalidBlockOutline />
			</PluginSidebar>
		</>
	);
}

registerPlugin(
	PLUGIN_NAME,
	{
		icon: MoreMenuIcon,
		render: AMPBlockValidation,
	},
);

addFilter( 'editor.BlockEdit', 'ampBlockValidation/filterEdit', withAMPToolbarButton, -99 );
