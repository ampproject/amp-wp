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

const Handle = styled.button.attrs( { type: 'button' } )`
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

	// On mouse move, check difference since last record vertical mouse position
	// and invoke callback with this difference.
	// Then record new vertical mouse position for next iteration.
	const handleMouseMove = useCallback( ( evt ) => {
		const delta = lastPosition.current - evt.pageY;
		handleHeightChange( delta );
		lastPosition.current = evt.pageY;
	}, [ handleHeightChange ] );

	// On mouse up, set dragging as false
	// - will cause useLayoutEffect to unregister listeners.
	const handleMouseUp = useCallback( () => setIsDragging( false ), [] );

	// On mouse down, set dragging as true
	// - will cause useLayoutEffect to register listeners.
	// Also record the initial vertical mouse position on the page.
	const handleMouseDown = useCallback( ( evt ) => {
		lastPosition.current = evt.pageY;
		setIsDragging( true );
	}, [] );

	// On initial render *and* every time `isDragging` changes value,
	// register all relevant listeners. Note that all listeners registered
	// will be correctly unregistered due to the cleanup function.
	useLayoutEffect( () => {
		const element = handle.current;
		const doc = element.ownerDocument;
		element.addEventListener( 'mousedown', handleMouseDown );

		if ( isDragging && doc ) {
			doc.addEventListener( 'mousemove', handleMouseMove );
			doc.addEventListener( 'mouseup', handleMouseUp );
		}

		return () => {
			if ( element ) {
				element.removeEventListener( 'mousedown', handleMouseDown );
				if ( isDragging && doc ) {
					doc.removeEventListener( 'mousemove', handleMouseMove );
					doc.removeEventListener( 'mouseup', handleMouseUp );
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
