/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import './style.css';

/**
 * A component providing different visual states depending on whether the element is selectable.
 *
 * @param {Object}  props             Component props.
 * @param {any}     props.children    Component children.
 * @param {string}  props.className   Extra classes to add to the element.
 * @param {string}  props.direction   The direction in which the visual treatment of the selectable element will orient.
 * @param {string}  props.ElementName The HTML element to serve as the selectable wrapper.
 * @param {boolean} props.selected    Whether the element is selected.
 */
export function Selectable( { children, className = '', direction = 'left', ElementName = 'div', selected = false, ...props } ) {
	return (
		<ElementName
			className={ classnames(
				className, 'selectable', { 'selectable--selected': selected }, `selectable--${ direction }`,
			) }
			{ ...props }
		>
			{ children }
		</ElementName>
	);
}

Selectable.propTypes = {
	children: PropTypes.any,
	className: PropTypes.string,
	direction: PropTypes.oneOf( [ 'top', 'right', 'bottom', 'left' ] ),
	ElementName: PropTypes.node,
	selected: PropTypes.bool,
};
