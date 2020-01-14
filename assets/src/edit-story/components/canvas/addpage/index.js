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
import { useStory } from '../../../app';
import { createPage } from '../../../elements';
import Icon from './plus.svg';

const Wrapper = styled.div`
	display: flex;
	align-items: center;
	justify-content: flex-start;
	height: 100%;
	color:  ${ ( { theme } ) => theme.colors.fg.v1 };
`;

const Space = styled.div`
	flex: 0 1 60px;
`;

const Circle = styled.a`
	color: inherit;
	height: 60px;
	width: 60px;
	flex: 0 0 60px;
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
	const { actions: { addPage } } = useStory();
	const handleClick = useCallback( () => {
		addPage( { page: createPage() } );
	}, [ addPage ] );
	return (
		<Wrapper>
			<Space />
			<Circle onClick={ handleClick }>
				<Icon />
			</Circle>
		</Wrapper>
	);
}

export default AddPage;
