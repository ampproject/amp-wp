/**
 * External dependencies
 */
import PropTypes from 'prop-types';

export default function DiffLine( { isDel, isIns, children, ...rest } ) {
	let TagName = 'span';

	if ( isIns ) {
		TagName = 'ins';
	} else if ( isDel ) {
		TagName = 'del';
	}

	return (
		<TagName { ...rest }>
			{ children }
		</TagName>
	);
}
DiffLine.propTypes = {
	isDel: PropTypes.bool,
	isIns: PropTypes.bool,
	children: PropTypes.any,
};
