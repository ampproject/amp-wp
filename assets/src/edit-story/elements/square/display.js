/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import {
	ElementFillContent,
	ElementWithBackgroundColor,
} from '../shared';

const Element = styled.div`
	${ ElementFillContent }
	${ ElementWithBackgroundColor }
`;

function SquareDisplay( { backgroundColor, width, height } ) {
	const props = {
		backgroundColor,
		width,
		height,
	};
	return (
		<Element { ...props } />
	);
}

SquareDisplay.propTypes = {
	backgroundColor: PropTypes.string,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
};

export default SquareDisplay;
