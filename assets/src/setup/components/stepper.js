/**
 * External dependencies
 */
import PropTypes from 'prop-types';

export function Stepper( { activePageIndex, pages } ) {
	return (
		<ul>
			{ pages.map( ( { navTitle }, index ) => (
				<li key={ `navigation-item-${ index }` }>
					{ activePageIndex === index ? navTitle : '' }
				</li>
			) ) }
		</ul>
	);
}

Stepper.propTypes = {
	activePageIndex: PropTypes.number.isRequired,
	pages: PropTypes.arrayOf(
		PropTypes.shape( {
			navTitle: PropTypes.string.isRequired,
		} ),
	).isRequired,
};
