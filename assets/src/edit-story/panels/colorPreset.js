/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Panel, PanelTitle, PanelContent } from './panel';

function ColorPresetPanel() {
	return (
		<Panel>
			<PanelTitle isPrimary>
				{ __( 'Color presets', 'amp' ) }
			</PanelTitle>
			<PanelContent>
				<p>
					{ __( 'Color presets go here', 'amp' ) }
				</p>
			</PanelContent>
		</Panel>
	);
}

export default ColorPresetPanel;
