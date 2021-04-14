/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { PluginPrePublishPanel } from '@wordpress/edit-post';

/**
 * Internal dependencies
 */
import AMPDocumentStatusNotification from '../components/amp-document-status';

export const PLUGIN_NAME = 'amp-block-validation-pre-publish-panel';
export const PLUGIN_ICON = '';

/**
 * AMP block validation pre-publish panel.
 */
export default function AMPPrePublishPanel() {
	return (
		<PluginPrePublishPanel
			title={ __( 'AMP', 'amp' ) }
			initialOpen={ true }
		>
			<AMPDocumentStatusNotification />
		</PluginPrePublishPanel>
	);
}
