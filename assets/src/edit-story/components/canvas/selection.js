/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import useCanvas from '../canvas/useCanvas';
import SingleSelectionMovable from './singleSelectionMovable';
import MultiSelectionMovable from './multiSelectionMovable';

function Selection() {
	const {
		state: { selectedElements },
	} = useStory();
	const {
		state: { editingElement, lastSelectionEvent, nodesById },
	} = useCanvas();

	// Do not show selection for in editing mode.
	if ( editingElement ) {
		return null;
	}

	// No selection.
	if ( selectedElements.length === 0 ) {
		return null;
	}

	// Single selection.
	if ( selectedElements.length === 1 ) {
		const selectedElement = selectedElements[ 0 ];
		const target = nodesById[ selectedElement.id ];
		if ( ! target ) {
			// Target not ready yet.
			return null;
		}
		return (
			<SingleSelectionMovable
				selectedElement={ selectedElement }
				targetEl={ target }
				pushEvent={ lastSelectionEvent }
			/>
		);
	}

	// Multi-selection.
	return (
		<MultiSelectionMovable
			selectedElements={ selectedElements }
			nodesById={ nodesById }
		/>
	);
}

export default Selection;
