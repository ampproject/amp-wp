/**
 * Internal dependencies
 */
import { Panel, PanelTitle, PanelContent } from './panel';

function ColorPresetPanel() {
	return (
		<Panel>
			<PanelTitle isPrimary>
				{ 'Color presets' }
			</PanelTitle>
			<PanelContent>
				<p>Color presets go here</p>
			</PanelContent>
		</Panel>
	);
}

export default ColorPresetPanel;
