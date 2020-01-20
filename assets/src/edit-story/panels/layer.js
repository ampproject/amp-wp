/**
 * Internal dependencies
 */
import { Panel, PanelTitle, PanelContent } from './panel';

function LayerPanel() {
	return (
		<Panel isResizable>
			<PanelTitle>
				{ 'Layers' }
			</PanelTitle>
			<PanelContent>
				<p>Layer stuff here</p>
			</PanelContent>
		</Panel>
	);
}

export default LayerPanel;
