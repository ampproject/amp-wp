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

function SquareDisplay( { backgroundColor } ) {
	const props = {
		backgroundColor,
	};
	return (
		<Element { ...props } />
	);
}

SquareDisplay.propTypes = {
	backgroundColor: PropTypes.string,
};

export default SquareDisplay;
