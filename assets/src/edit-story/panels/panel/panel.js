/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

const Wrapper = styled.section``;

const Form = styled.form`
	display: flex;
	flex-direction: column;
`;

function Panel( { children } ) {
	return (
		<Wrapper>
			<Form>
				{ children }
			</Form>
		</Wrapper>
	);
}

Panel.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default Panel;
