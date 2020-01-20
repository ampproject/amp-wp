/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import {
	useRef,
	useState,
	useCallback,
	useLayoutEffect,
} from '@wordpress/element';

const Handle = styled.button`
	background: transparent;
	border: 0;
	padding: 0;
	position: absolute;
	top: 0;
	left: 50%;
	margin-left: -60px;
	width: 120px;
	height: 20px;
	display: flex;
	flex-direction: column;
	justify-content: flex-start;
	align-items: center;
	cursor: row-resize;
`;

const Bar = styled.div`
	margin-top: 4px;
	background-color: black;
	width: 32px;
	height: 4px;
	border-radius: 2px;
`;

function DragHandle( { handleHeightChange } ) {
	const handle = useRef();
	const lastPosition = useRef();
	const [ isDragging, setIsDragging ] = useState( false );
	const handleMouseMove = useCallback( ( evt ) => {
		const delta = lastPosition.current - evt.pageY;
		handleHeightChange( delta );
		lastPosition.current = evt.pageY;
	}, [ handleHeightChange ] );

	const handleMouseUp = useCallback( () => setIsDragging( false ), [] );

	const handleMouseDown = useCallback( ( evt ) => {
		lastPosition.current = evt.pageY;
		setIsDragging( true );
	}, [] );

	useLayoutEffect( () => {
		const element = handle.current;
		element.addEventListener( 'mousedown', handleMouseDown );

		if ( isDragging && element.ownerDocument ) {
			element.ownerDocument.addEventListener( 'mousemove', handleMouseMove );
			element.ownerDocument.addEventListener( 'mouseup', handleMouseUp );
		}

		return () => {
			if ( element ) {
				element.removeEventListener( 'mousedown', handleMouseDown );
				if ( isDragging && element.ownerDocument ) {
					element.ownerDocument.removeEventListener( 'mousemove', handleMouseMove );
					element.ownerDocument.removeEventListener( 'mouseup', handleMouseUp );
				}
			}
		};
	}, [ isDragging, handleMouseUp, handleMouseMove, handleMouseDown ] );

	return (
		<Handle ref={ handle }>
			<Bar />
		</Handle>
	);
}

DragHandle.propTypes = {
	handleHeightChange: PropTypes.func.isRequired,
};

export default DragHandle;
