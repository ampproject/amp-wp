/**
 * External dependencies
 */
import Moveable from 'react-moveable';

/**
 * WordPress dependencies
 */
import { forwardRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import InOverlay from '../overlay';

const DEFAULT_Z_INDEX = 10;

function MovableWithRef( { ...moveableProps }, ref ) {
	return (
		<InOverlay
			zIndex={ DEFAULT_Z_INDEX }
			render={ ( { container } ) => {
				return (
					<Moveable
						ref={ ref }
						container={ container }
						{ ...moveableProps }
					/>
				);
			} } />
	);
}

const Movable = forwardRef( MovableWithRef );

export default Movable;
