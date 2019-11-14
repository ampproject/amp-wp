/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useStory from '../../story';
import Icon from './plus.svg';

const Wrapper = styled.div`
	display: flex;
	align-items: center;
	justify-content: flex-start;
	height: 100%;
	color:  ${ ( { theme } ) => theme.colors.fg.v1 };
`;

const Circle = styled.a`
	margin: 0 0 0 60px;
	color: inherit;
	height: 60px;
	width: 60px;
	border-radius: 50%;
	border: 2px solid;
	display: flex;
	align-items: center;
	justify-content: center;
	opacity: .25;
	cursor: pointer;

	&:hover {
		color: inherit;
		opacity: 1;
	}
`;

function AddPage() {
	const { actions: { addBlankPage, setCurrentPageById } } = useStory();
	const handleClick = useCallback( () => {
		const page = addBlankPage();
		setCurrentPageById( page.clientId );
	}, [ addBlankPage, setCurrentPageById ] );
	return (
		<Wrapper>
			<Circle onClick={ handleClick }>
				<Icon />
			</Circle>
		</Wrapper>
	);
}

export default AddPage;
