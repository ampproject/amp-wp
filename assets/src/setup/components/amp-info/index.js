/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.css';

/**
 * A component providing a UI for helpful info.
 *
 * @param {Object} props Component props.
 * @param {?string} props.children Notice content, not including the icon.
 * @param {?string} props.className Optional extra class names.
 * @param {?Object} props.icon An SVG icon Component
 */
export function AMPInfo( { children, className, icon: Icon } ) {
	const classNames = [
		className ? className : '',
		'amp-info',
	].filter( ( item ) => item );

	return (
		<div className={ classNames.join( ' ' ) }>
			{ Icon && <Icon className="amp-info__icon" /> }
			{ children }
		</div>
	);
}

AMPInfo.propTypes = {
	children: PropTypes.node,
	className: PropTypes.string,
	icon: PropTypes.func,
};
