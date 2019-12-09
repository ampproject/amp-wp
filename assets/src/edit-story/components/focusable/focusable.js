/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import MouseTrap from 'mousetrap';
import 'mousetrap/plugins/global-bind/mousetrap-global-bind';

/**
 * WordPress dependencies
 */
import { useLayoutEffect, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useCombinedRefs from '../../utils/useCombinedRefs';
import getValidDOMAttributes from '../../utils/getValidDOMAttributes';

function Focusable( { shortcuts, hasFocus, isGlobal, element, className, role, tabindex, forwardedRef, children, ...rest } ) {
	const ref = useRef();
	const setRef = useCombinedRefs( ref, forwardedRef );

	useLayoutEffect( () => {
		const mt = MouseTrap( ref.current );

		// Bind all listeners
		const sequences = Object.keys( shortcuts );
		sequences.forEach( ( sequence ) => {
			const callback = shortcuts[ sequence ];
			const bindFn = isGlobal ? 'bindGlobal' : 'bind';
			mt[ bindFn ]( sequence, callback );
		} );

		// Clear listeners on unmount/remount
		return () => mt.reset();
	}, [ isGlobal, shortcuts ] );

	// Force focus if relevant
	useLayoutEffect( () => {
		if ( hasFocus ) {
			ref.current.focus();
		}
	}, [ hasFocus ] );

	const Wrapper = element;

	return (
		<Wrapper ref={ setRef } className={ className } role={ role } tabIndex={ tabindex } { ...getValidDOMAttributes( rest ) }>
			{ children }
		</Wrapper>
	);
}

Focusable.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ),
	forwardedRef: PropTypes.object,

	shortcuts: PropTypes.object,
	hasFocus: PropTypes.bool,
	isGlobal: PropTypes.bool,

	element: PropTypes.string,
	className: PropTypes.string,
	role: PropTypes.string,
	tabindex: PropTypes.number,
};

Focusable.defaultProps = {
	shortcuts: {},
	isGlobal: false,
	hasFocus: false,

	element: 'div',
	className: '',
	role: 'button',
	tabindex: 0,
};

export default Focusable;
