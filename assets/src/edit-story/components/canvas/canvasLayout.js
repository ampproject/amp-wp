/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import EditLayer from './editLayer';
import DisplayLayer from './displayLayer';
import FramesLayer from './framesLayer';
import NavLayer from './navLayer';
import SelectionCanvas from './selectionCanvas';
import { useLayoutParams, useLayoutParamsCssVars } from './layout';

const Background = styled.div`
	background-color: ${ ( { theme } ) => theme.colors.bg.v1 };
	width: 100%;
	height: 100%;
	position: relative;
	user-select: none;
`;

function CanvasLayout() {
	const backgroundRef = useRef( null );

	useLayoutParams( backgroundRef );
	const layoutParamsCss = useLayoutParamsCssVars();

	return (
		<Background
			ref={ backgroundRef }
			style={ layoutParamsCss }>
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
