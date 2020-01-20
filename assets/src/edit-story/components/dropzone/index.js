/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useRef, useState, useLayoutEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useDropZone from './useDropZone';

const DropZoneComponent = styled.div`
	display: inherit;
	position: relative;
	${ ( { borderPosition, theme, highlightWidth } ) => borderPosition && `
		:after {
			height: 100%;
			display: block;
			position: absolute;
			width: ${ highlightWidth }px;
			background: ${ theme.colors.action };
			content: '';
			${ borderPosition }: -${ highlightWidth / 2 }px;
		}
	` }
`;

function DropZone( { children, onDrop, pageIndex } ) {
	const dropZoneElement = useRef( null );
	const [ dropZone, setDropZone ] = useState( null );
	const { actions: { registerDropZone, unregisterDropZone, resetHoverState }, state: { hoveredDropZone } } = useDropZone();

	useLayoutEffect( () => {
		setDropZone( {
			node: dropZoneElement.current,
		} );
	}, [ dropZoneElement ] );

	useLayoutEffect( () => {
		registerDropZone( dropZone );
		return () => {
			unregisterDropZone( dropZone );
		};
	}, [ dropZone, registerDropZone, unregisterDropZone ] );

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
		resetHoverState();
		if ( dropZoneElement.current ) {
			const rect = dropZoneElement.current.getBoundingClientRect();
			// Get the relative position of the dropping point based on the dropzone.
			const relativePosition = {
				x: evt.clientX - rect.left < rect.right - evt.clientX ? 'left' : 'right',
				y: evt.clientY - rect.top < rect.bottom - evt.clientY ? 'top' : 'bottom',
			};
			if ( 'default' === getDragType( evt ) ) {
				onDrop( evt, { position: relativePosition, pageIndex } );
			}
			// @todo Support for files when it becomes necessary.
		}
		evt.preventDefault();
	};

	const isDropZoneActive = dropZoneElement.current && hoveredDropZone && hoveredDropZone.node === dropZoneElement.current;
	// @todo Currently static, can be adjusted for other use cases.
	const highlightWidth = 4;
	return (
		<DropZoneComponent highlightWidth={ highlightWidth } borderPosition={ isDropZoneActive ? hoveredDropZone.position.x : null } ref={ dropZoneElement } onDrop={ onDropHandler }>
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
	pageIndex: PropTypes.number,
};

export default DropZone;
