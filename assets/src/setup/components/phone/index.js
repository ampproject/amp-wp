/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.css';

/**
 * Component resembling a phone with a screen.
 *
 * @param {Object} props Component props.
 * @param {any} props.children The elements to display in the screen.
 */
export function Phone( { children } ) {
	return (
		<div className="phone">
			{ children }
		</div>
	);
}

Phone.propTypes = {
	children: PropTypes.any,
};
