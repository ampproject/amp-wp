/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useCallback, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

function DropZoneProvider( { children } ) {
	const [ dropZones, setDropZones ] = useState( [] );
	const [ hoveredDropZone, setHoveredDropZone ] = useState( null );

	const registerDropZone = useCallback(
		( dropZone ) => {
			// If dropZone isn't registered yet.
			if ( dropZone && ! dropZones.some( ( { node } ) => node === dropZone.node ) ) {
				setDropZones( ( oldDropZones ) => ( [ ...oldDropZones, dropZone ] ) );
			}
		}, [ dropZones ] );

	// Unregisters dropzones which node's don't exist.
	const unregisterDropZone = useCallback(
		( dropZone ) => {
			// If dropZone needs unregistering.
			if ( dropZones.some( ( dz ) => dz === dropZone ) ) {
				setDropZones( ( oldDropZones ) => oldDropZones.filter( ( dz ) => dz !== dropZone ) );
			}
		}, [ dropZones ] );

	const isWithinElementBounds = ( element, x, y ) => {
		const rect = element.getBoundingClientRect();
		if ( rect.bottom === rect.top || rect.left === rect.right ) {
			return false;
		}
		return x >= rect.left && x <= rect.right && y >= rect.top && y <= rect.bottom;
	};

	const resetHoverState = () => {
		setHoveredDropZone( null );
	};

	const onDragOver = ( evt ) => {
		evt.preventDefault();
		// Get the hovered dropzone. // @todo Consider dropzone inside dropzone, will we need this?
		const foundDropZones = dropZones.filter( ( dropZone ) => {
			return isWithinElementBounds( dropZone.node, evt.clientX, evt.clientY );
		} );

		// If there was a dropzone before and nothing was found now, reset.
		if ( hoveredDropZone && ! foundDropZones.length ) {
			resetHoverState();
			return;
		}
		const foundDropZone = foundDropZones[ 0 ];
		// If dropzone not found, do nothing.
		if ( ! foundDropZone || ! foundDropZone.node ) {
			return;
		}
		const rect = foundDropZone.node.getBoundingClientRect();

		const position = {
			x: evt.clientX - rect.left < rect.right - evt.clientX ? 'left' : 'right',
			y: evt.clientY - rect.top < rect.bottom - evt.clientY ? 'top' : 'bottom',
		};

		setHoveredDropZone( {
			node: foundDropZone.node,
			position,
		} );
	};

	const state = {
		state: {
			hoveredDropZone,
			dropZones,
		},
		actions: {
			registerDropZone,
			unregisterDropZone,
			resetHoverState,
		},
	};
	return (
		<div onDragOver={ onDragOver }>
			<Context.Provider value={ state }>
				{ children }
			</Context.Provider>
		</div>
	);
}

DropZoneProvider.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default DropZoneProvider;
