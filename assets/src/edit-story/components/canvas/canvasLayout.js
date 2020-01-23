/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useRef, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import EditLayer from './editLayer';
import DisplayLayer from './displayLayer';
import FramesLayer from './framesLayer';
import NavLayer from './navLayer';
import SelectionCanvas from './selectionCanvas';
import useResizeEffect from '../../utils/useResizeEffect';
import useCanvas from './useCanvas';

const Background = styled.div`
	background-color: ${ ( { theme } ) => theme.colors.bg.v1 };
	width: 100%;
	height: 100%;
	position: relative;
	user-select: none;
`;

// export const PAGE_WIDTH = 412;
// export const PAGE_HEIGHT = 732;
// export const PAGE_WIDTH = 268;
// export const PAGE_HEIGHT = 476;

function CanvasLayout() {
	const backgroundRef = useRef( null );

	const {
		state: { pageSize },
		actions: { setPageSize },
	} = useCanvas();

	useResizeEffect(backgroundRef, ({width, height}) => {
    console.log('QQQQ: resize: ', {width, height});
    if (height >= 850) {
    	console.log('QQQQ: setPageSize: ', {width: 412, height: 732});
    	setPageSize({width: 412, height: 732});
    } else {
    	console.log('QQQQ: setPageSize: ', {width: 268, height: 476});
    	//QQQQ
    	// setPageSize({width: 268, height: 476});
    }
	});

	return (
		<Background
				ref={backgroundRef}
				style={{
					'--page-width-px': `${pageSize.width}px`,
					'--page-height-px': `${pageSize.height}px`,
				}}>
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
