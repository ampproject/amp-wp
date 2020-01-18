/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import EditLayer from './editLayer';
import DisplayLayer from './page';
import FramesLayer from './framesLayer';
import NavLayer from './navLayer';
import SelectionCanvas from './selectionCanvas';

const Background = styled.div`
	background-color: ${ ( { theme } ) => theme.colors.bg.v1 };
	width: 100%;
	height: 100%;
	position: relative;
	user-select: none;
`;

function CanvasLayout() {
	return (
		<Background>
			<SelectionCanvas>
				<DisplayLayer />
				<NavLayer />
				<FramesLayer />
			</SelectionCanvas>
			<EditLayer />
		</Background>
	);
}

export default CanvasLayout;
