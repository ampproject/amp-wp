/**
 * Internal dependencies
 */
import { Panel, PanelTitle, PanelContent } from './panel';

function LayerPanel() {
	return (
		<Panel initialHeight={ 240 }>
			<PanelTitle isPrimary isResizable>
				{ 'Layers' }
			</PanelTitle>
			<PanelContent>
				<p>Layer stuff here</p>
			</PanelContent>
		</Panel>
	);
}

export default LayerPanel;
