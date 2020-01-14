/**
 * Internal dependencies
 */
import InspectorProvider from './inspectorProvider';
import InspectorLayout from './inspectorLayout';

function Inspector() {
	return (
		<InspectorProvider>
			<InspectorLayout />
		</InspectorProvider>
	);
}

export default Inspector;
