/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Panel, PanelTitle, PanelContent } from './panel';

function LayerPanel() {
	return (
		<Panel initialHeight={ 240 }>
			<PanelTitle isPrimary isResizable>
				{ __( 'Layers', 'amp' ) }
			</PanelTitle>
			<PanelContent>
				<p>
					{ __( 'Layer contents', 'amp' ) }
				</p>
			</PanelContent>
		</Panel>
	);
}

export default LayerPanel;
