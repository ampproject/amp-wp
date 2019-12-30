/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useEffect, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import useCanvas from '../canvas/useCanvas';
import withOverlay from '../overlay/withOverlay';
import InOverlay from '../overlay';

const LasoMode = {
	OFF: 0,
	ON: 1,
	MAYBE: 2,
};

const Container = withOverlay( styled.div`
  width: 100%;
  height: 100%;
  user-select: none;
` );

const Laso = styled.div`
  display: none;
  position: absolute;
  border: 1px dotted ${ ( { theme } ) => theme.colors.selection };
  z-index: 1;
`;

function SelectionCanvas( { children } ) {
	const {
		actions: { clearSelection },
	} = useStory();

	const {
		state: { pageContainer },
		actions: { clearEditing, selectIntersection },
	} = useCanvas();

	const overlayRef = useRef( null );
	const lasoRef = useRef( null );
	const offsetRef = useRef( [ 0, 0 ] );
	const startRef = useRef( [ 0, 0 ] );
	const endRef = useRef( [ 0, 0 ] );
	const lasoModeRef = useRef( LasoMode.OFF );

	const getLasoBox = () => {
		const [ x1, y1 ] = startRef.current;
		const [ x2, y2 ] = endRef.current;
		const x = Math.min( x1, x2 );
		const y = Math.min( y1, y2 );
		const w = Math.abs( x1 - x2 );
		const h = Math.abs( y1 - y2 );
		return [ x, y, w, h ];
	};

	const updateLaso = () => {
		const laso = lasoRef.current;
		if ( ! laso ) {
			return;
		}
		const lasoMode = lasoModeRef.current;
		const [ x, y, w, h ] = getLasoBox();
		laso.style.left = `${ x }px`;
		laso.style.top = `${ y }px`;
		laso.style.width = `${ w }px`;
		laso.style.height = `${ h }px`;
		laso.style.display = lasoMode === LasoMode.ON ? 'block' : 'none';
	};

	const onMouseDown = ( evt ) => {
		clearSelection();
		clearEditing();

		const overlay = overlayRef.current;
		let offsetX = 0,
			offsetY = 0;
		for ( let offsetNode = overlay; offsetNode; offsetNode = offsetNode.offsetParent ) {
			offsetX += offsetNode.offsetLeft;
			offsetY += offsetNode.offsetTop;
		}
		const x = evt.pageX - offsetX;
		const y = evt.pageY - offsetY;
		offsetRef.current = [ offsetX, offsetY ];
		startRef.current = [ x, y ];
		endRef.current = [ x, y ];
		lasoModeRef.current = LasoMode.MAYBE;
		updateLaso();
	};

	const onMouseMove = ( evt ) => {
		if ( lasoModeRef.current === LasoMode.OFF ) {
			return;
		}
		const [ offsetX, offsetY ] = offsetRef.current;
		const x2 = evt.pageX - offsetX;
		const y2 = evt.pageY - offsetY;
		endRef.current[ 0 ] = x2;
		endRef.current[ 1 ] = y2;
		lasoModeRef.current = LasoMode.ON;
		updateLaso();
	};

	const onMouseUp = ( ) => {
		if ( lasoModeRef.current === LasoMode.ON ) {
			const [ ox, oy, width, height ] = getLasoBox();
			const x = ox - pageContainer.offsetLeft;
			const y = oy - pageContainer.offsetTop;
			clearSelection();
			clearEditing();
			selectIntersection( { x, y, width, height } );
		}
		lasoModeRef.current = LasoMode.OFF;
		updateLaso();
	};

	useEffect( updateLaso );

	return (
		<Container
			onMouseDown={ onMouseDown }
			onMouseMove={ onMouseMove }
			onMouseUp={ onMouseUp }
		>
			{ children }
			<InOverlay ref={ overlayRef }>
				<Laso ref={ lasoRef } />
			</InOverlay>
		</Container>
	);
}

SelectionCanvas.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default SelectionCanvas;
