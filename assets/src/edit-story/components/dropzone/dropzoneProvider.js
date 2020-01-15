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

function DropzoneProvider( { children } ) {
	const [ dropZones, setDropZones ] = useState( [] );
	const [ hoveredDropZone, setHoveredDropZone ] = useState( null );

	const addDropZone = useCallback(
		( dropZone ) => {
			setDropZones( ( oldDropZones ) => ( [ ...oldDropZones, dropZone ] ) );
		}, [] );

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
		const foundDropZones = dropZones.filter( ( dropZone ) => isWithinElementBounds( dropZone.ref, evt.clientX, evt.clientY ) );

		// If there was a dropzone before and nothing was found now, reset.
		if ( hoveredDropZone && ! foundDropZones.length ) {
			resetHoverState();
			return;
		}
		const foundDropZone = foundDropZones[ 0 ];
		// If dropzone not found, do nothing.
		if ( ! foundDropZone || ! foundDropZone.ref ) {
			return;
		}
		const rect = foundDropZone.ref.getBoundingClientRect();

		const position = {
			x: evt.clientX - rect.left < rect.right - evt.clientX ? 'left' : 'right',
			y: evt.clientY - rect.top < rect.bottom - evt.clientY ? 'top' : 'bottom',
		};

		setHoveredDropZone( {
			ref: foundDropZone.ref,
			position,
		} );
	};

	const state = {
		state: {
			hoveredDropZone,
			dropZones,
		},
		actions: {
			addDropZone,
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

DropzoneProvider.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default DropzoneProvider;
