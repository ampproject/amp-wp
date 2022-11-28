/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.css';

/**
 * Component resembling a desktop computer with a screen.
 *
 * @param {Object} props          Component props.
 * @param {any}    props.children The elements to display in the screen.
 */
export function Desktop( { children } ) {
	return (
		<div className="desktop">
			<div className="desktop__toolbar">
				<span />
				<span />
				<span />
			</div>
			{ children }
		</div>
	);
}

Desktop.propTypes = {
	children: PropTypes.any,
};
