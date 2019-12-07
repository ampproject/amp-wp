/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useRef, useLayoutEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import getDirectionalKeysForNode from '../../utils/getDirectionalKeysForNode';
import Focusable from './focusable';

function NavigableGroup( { direction, element, className, role, tabindex, children } ) {
	const ref = useRef();
	const [ shortcuts, setShortcuts ] = useState( {} );

	useLayoutEffect( () => {
		const focus = ( node ) => node && node.focus();

		if ( direction === NavigableGroup.DIRECTION_HORIZONTAL ) {
			const { forward, backward } = getDirectionalKeysForNode( ref.current );
			setShortcuts( {
				[ forward ]: ( evt ) => focus( evt.target.nextElementSibling ),
				[ backward ]: ( evt ) => focus( evt.target.previousElementSibling ),
			} );
		}
	}, [ direction ] );

	return (
		<Focusable shortcuts={ shortcuts } forwardedRef={ ref } element={ element } className={ className } role={ role } tabindex={ tabindex }>
			{ children }
		</Focusable>
	);
}

NavigableGroup.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,

	direction: PropTypes.string,
	hotkey: PropTypes.string,
	element: PropTypes.string,
	className: PropTypes.string,
	role: PropTypes.string,
	tabindex: PropTypes.number,
};

export default NavigableGroup;

NavigableGroup.DIRECTION_HORIZONTAL = 'horizontal';
NavigableGroup.DIRECTION_VERTICAL = 'vertical';
NavigableGroup.DIRECTION_GRID = 'grid';
