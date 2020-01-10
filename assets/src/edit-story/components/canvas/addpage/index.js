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

const Button = styled.button`
	display: flex;
	align-items: center;
	justify-content: center;
	color: inherit;
	background: transparent;
	padding: 0;
	height: 100px;
	width: 56px;
	border: 1px dashed;
	opacity: .25;
	cursor: pointer;

	&:hover {
		color: inherit;
		opacity: 1;
	}

	svg {
		width: 1em;
		height: 1em;
	}
`;

function AddPage() {
	const { actions: { addPage } } = useStory();
	const handleClick = useCallback( () => {
		addPage( { page: createPage() } );
	}, [ addPage ] );
	return (
		<Wrapper>
			<Button onClick={ handleClick }>
				<Icon />
			</Button>
		</Wrapper>
	);
}

export default AddPage;
