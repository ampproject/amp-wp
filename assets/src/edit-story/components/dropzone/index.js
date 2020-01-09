/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useRef } from '@wordpress/element';

const DropZoneComponent = styled.div`
	display: inherit;
`;

function DropZone( { children, onDrop } ) {
	const dropZoneElement = useRef( null );
	const onDragOver = ( evt ) => {
		// @todo Display highlighted if dragging over?
		evt.preventDefault();
	};

	const getDragType = ( { dataTransfer } ) => {
		if ( dataTransfer ) {
			// @todo Support Edge since types is DomStringList there.
			if ( dataTransfer.types.includes( 'Files' ) ) {
				return 'file';
			}
			if ( dataTransfer.types.includes( 'text/html' ) ) {
				return 'html';
			}
		}
		return 'default';
	};

	const onDropHandler = ( evt ) => {
		if ( dropZoneElement.current ) {
			const rect = dropZoneElement.current.getBoundingClientRect();
			// Get the relative position of the dropping point based on the dropzone.
			const position = {
				x: evt.clientX - rect.left < rect.right - evt.clientX ? 'left' : 'right',
				y: evt.clientY - rect.top < rect.bottom - evt.clientY ? 'top' : 'bottom',
			};
			if ( 'default' === getDragType( evt ) ) {
				onDrop( evt, position );
			}
			// @todo Support for files when it becomes necessary.
		}
		evt.preventDefault();
	};

	return (
		<DropZoneComponent ref={ dropZoneElement } onDrop={ onDropHandler } onDragOver={ onDragOver } >
			{ children }
		</DropZoneComponent>
	);
}

DropZone.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
	onDrop: PropTypes.func,
};

export default DropZone;
