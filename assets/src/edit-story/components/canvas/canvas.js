/**
 * External dependencies
 */
import styled from 'styled-components';

const Background = styled.div`
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	height: 100%;
`;

const Page = styled.div`
	background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	height: 775px;
	width: 434px;
`;

function Canvas() {
	return (
		<Background>
			<Page />
		</Background>
	);
}

export default Canvas;
