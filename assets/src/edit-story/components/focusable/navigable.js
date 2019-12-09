/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import Focusable from './focusable';

function Navigable( { element, className, role, tabindex, children, ...rest } ) {
	return (
		<Focusable element={ element } className={ className } role={ role } tabindex={ tabindex } { ...rest }>
			{ children }
		</Focusable>
	);
}

Navigable.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ),

	element: PropTypes.string,
	className: PropTypes.string,
	role: PropTypes.string,
	tabindex: PropTypes.number,
};

export default Navigable;
