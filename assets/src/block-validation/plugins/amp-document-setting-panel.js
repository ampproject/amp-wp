/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';

/**
 * Internal dependencies
 */
import AMPDocumentStatusNotification from '../components/amp-document-status';

export const PLUGIN_NAME = 'amp-block-validation-document-setting-panel';
export const PLUGIN_ICON = '';

/**
 * AMP block validation document settings panel.
 */
export default function AMPDocumentSettingPanel() {
	return (
		<PluginDocumentSettingPanel
			name={ PLUGIN_NAME }
			title={ __( 'AMP', 'amp' ) }
			initialOpen={ true }
		>
			<AMPDocumentStatusNotification />
		</PluginDocumentSettingPanel>
	);
}
