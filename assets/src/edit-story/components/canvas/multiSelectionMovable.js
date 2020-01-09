/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useRef, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Movable from '../movable';

function MultiSelectionMovable( { selectedElements, nodesById } ) {
	const moveable = useRef();

	// Update moveable with whatever properties could be updated outside moveable
	// itself.
	useEffect( () => {
		if ( moveable.current ) {
			moveable.current.updateRect();
		}
	} );

	const targets = selectedElements.map( ( element ) => nodesById[ element.id ] );
	// Not all targets have been defined yet.
	if ( targets.includes( undefined ) ) {
		return null;
	}
	return (
		<Movable
			ref={ moveable }
			zIndex={ 0 }
			targets={ targets }

			// todo@: implement group transformations.
			draggable={ true }
			resizable={ true }
			rotatable={ true }
		/>
	);
}

MultiSelectionMovable.propTypes = {
	selectedElements: PropTypes.arrayOf( PropTypes.object ).isRequired,
	nodesById: PropTypes.object.isRequired,
};

export default MultiSelectionMovable;
