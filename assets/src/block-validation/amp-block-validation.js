/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY, createStore } from './store';
import { ToolbarIcon, MoreMenuIcon } from './icon';
import { Sidebar } from './sidebar';
import { InvalidBlockOutline } from './invalid-block-outline';
import { BlockValidationStateUpdater } from './block-validation-state-updater';
import { PLUGIN_NAME, PLUGIN_TITLE, SIDEBAR_NAME } from '.';

/**
 * Provides a dedicated sidebar for the plugin, with toggle buttons in the editor toolbar and more menu.
 *
 * @param {Object} props
 * @param {Object} props.initialState Initial store state.
 */
export function AMPBlockValidation( { initialState } ) {
	useEffect( () => {
		createStore( initialState );
	}, [ initialState ] );

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
			<BlockValidationStateUpdater />
		</>
	);
}
AMPBlockValidation.propTypes = {
	initialState: PropTypes.object.isRequired,
};
