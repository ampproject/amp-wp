/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.css';

/**
 * A component providing different visual states depending on whether the element is selectable.
 *
 * @param {Object} props Component props.
 * @param {any} props.children Component children.
 * @param {string} props.className Extra classes to add to the element.
 * @param {string} props.direction The direction in which the visual treatment of the selectable element will orient.
 * @param {string} props.elementName The HTML element to serve as the selectable wrapper.
 * @param {boolean} props.selected Whether the element is selected.
 */
export function Selectable( { children, className = '', direction = 'left', elementName = 'div', selected = false, ...props } ) {
	const classNames = [ className, 'selectable', ( selected ? 'selectable--selected' : '' ), `selectable--${ direction }` ]
		.filter( ( name ) => name )
		.join( ' ' );

	return (
		<elementName
			className={ classNames }
			{ ...props }
		>
			{ children }
		</elementName>
	);
}

Selectable.propTypes = {
	children: PropTypes.any,
	className: PropTypes.string,
	direction: PropTypes.oneOf( [ 'top', 'right', 'bottom', 'left' ] ),
	elementName: PropTypes.node,
	selected: PropTypes.bool,
};
