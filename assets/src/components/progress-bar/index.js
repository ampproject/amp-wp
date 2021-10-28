/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * Progress bar component.
 *
 * @param {number} value The value of the progress bar indicator (between 0 and 100).
 */
export function ProgressBar( { value } ) {
	const style = {
		transform: `translateX(${ Math.max( value, 3 ) - 100 }%)`,
		transitionDuration: `${ value < 100 ? 800 : 200 }ms`,
	};

	return (
		<div
			className="progress-bar"
			role="progressbar"
			aria-valuenow={ value }
			aria-valuemin="0"
			aria-valuemax="100"
		>
			<div className="progress-bar__track">
				<div
					className="progress-bar__indicator"
					style={ style }
				/>
			</div>
		</div>
	);
}

ProgressBar.propTypes = {
	value: PropTypes.number.isRequired,
};
