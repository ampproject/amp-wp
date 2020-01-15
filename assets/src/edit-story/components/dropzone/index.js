/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useRef, useLayoutEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useDropZone from './useDropZone';

const DropZoneComponent = styled.div`
	display: inherit;
`;

function DropZone( { children, onDrop } ) {
	const dropZoneElement = useRef( null );
	const { actions: { addDropZone }, state: { hoveredDropZone } } = useDropZone();

	const isDropZoneActive = () => {
		return dropZoneElement.current && hoveredDropZone && hoveredDropZone.ref === dropZoneElement.current;
	};

	useLayoutEffect( () => {
		// @todo add only if already exists.
		addDropZone( {
			ref: dropZoneElement.current,
		} );
		// @todo Remove when doesn't exist anymore!
	}, [] );

	const getDragType = ( { dataTransfer } ) => {
		if ( dataTransfer ) {
			if ( Array.isArray( dataTransfer.types ) ) {
				if ( dataTransfer.types.includes( 'Files' ) ) {
					return 'file';
				}
				if ( dataTransfer.types.includes( 'text/html' ) ) {
					return 'html';
				}
			} else {
				// For Edge, types is DomStringList and not array.
				if ( dataTransfer.types.contains( 'Files' ) ) {
					return 'file';
				}
				if ( dataTransfer.types.contains( 'text/html' ) ) {
					return 'html';
				}
			}
		}
		return 'default';
	};

	const onDropHandler = ( evt ) => {
		if ( dropZoneElement.current ) {
			const rect = dropZoneElement.current.getBoundingClientRect();
			// Get the relative position of the dropping point based on the dropzone.
			const relativePosition = {
				x: evt.clientX - rect.left < rect.right - evt.clientX ? 'left' : 'right',
				y: evt.clientY - rect.top < rect.bottom - evt.clientY ? 'top' : 'bottom',
			};
			if ( 'default' === getDragType( evt ) ) {
				onDrop( evt, relativePosition );
			}
			// @todo Support for files when it becomes necessary.
		}
		evt.preventDefault();
	};

	// @todo Add border/outline for active dropzone.
	return (
		<DropZoneComponent ref={ dropZoneElement } onDrop={ onDropHandler }>
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
