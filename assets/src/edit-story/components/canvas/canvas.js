/**
 * Internal dependencies
 */
import CanvasLayout from './canvasLayout';
import CanvasProvider from './canvasProvider';

function Canvas() {
	return (
		<CanvasProvider>
			<CanvasLayout />
		</CanvasProvider>
	);
}

export default Canvas;
