/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.css';
import { Loading } from '../loading';

/**
 * Component resembling a phone with a screen.
 *
 * @param {Object}  props           Component props.
 * @param {any}     props.children  The elements to display in the screen.
 * @param {boolean} props.isLoading Flag indicating if the content is loading.
 */
export function Phone( { children, isLoading = false } ) {
	return (
		<div className={ `phone ${ isLoading ? 'is-loading' : '' }` }>
			<div className="phone__inner">
				<div className="phone__overlay">
					<Loading />
				</div>
				{ children }
			</div>
		</div>
	);
}

Phone.propTypes = {
	children: PropTypes.any,
	isLoading: PropTypes.bool,
};
