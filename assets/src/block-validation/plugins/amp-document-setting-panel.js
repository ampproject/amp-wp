/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';

/**
 * Internal dependencies
 */
import AMPDocumentStatusNotification from '../components/amp-document-status';

export const PLUGIN_NAME = 'amp-document-setting';
export const PLUGIN_ICON = '';

/**
 * AMP document settings panel plugin.
 */
export default function AMPDocumentSettingPanel() {
	return (
		<PluginDocumentSettingPanel title={ __( 'AMP', 'amp' ) }>
			<AMPDocumentStatusNotification />
		</PluginDocumentSettingPanel>
	);
}
