/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Summary screen when standard mode was selected.
 *
 * @param {Object} props
 * @param {Object} props.currentTheme Data for the theme currently active on the site.
 */
export function Standard() {
	return <div>
		standard
	</div>;
}

Standard.propTypes = {
	currentTheme: PropTypes.shape( {
		description: PropTypes.string,
		name: PropTypes.string,
		screenshot: PropTypes.string,
		url: PropTypes.string,
	} ).isRequired,
};
