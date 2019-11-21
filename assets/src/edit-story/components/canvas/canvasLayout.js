/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { CENTRAL_RIGHT_PADDING, PAGE_WIDTH, PAGE_HEIGHT } from '../../constants';
import useCanvas from './useCanvas';
import Page from './page';
import Meta from './meta';
import Carrousel from './carrousel';
import AddPage from './addpage';

const Background = styled.div`
	background-color: ${ ( { isPassive, theme } ) => isPassive ? theme.colors.bg.v5 : theme.colors.bg.v1 };
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	height: 100%;

	display: grid;
	grid:
    ".   meta       .        ." 1fr
    ".   page       addpage  ." ${ PAGE_HEIGHT }px
    ".   carrousel  .        ." 1fr
    / 1fr ${ PAGE_WIDTH }px 1fr ${ CENTRAL_RIGHT_PADDING }px;
`;

const Area = styled.div`
	grid-area: ${ ( { area } ) => area };
	height: 100%;
	width: 100%;

	${ ( { isPassive } ) => isPassive && `
		pointer-events: none;
		opacity: 0.4;
	` };
`;

function CanvasLayout() {
	const { state: { isEditing, backgroundClickHandler }, actions: { clearEditing } } = useCanvas();
	const onClick = isEditing ? clearEditing : backgroundClickHandler;
	return (
		<Background isPassive={ isEditing } onClick={ onClick || null }>
			<Area area="page">
				<Page />
			</Area>
			<Area area="meta" isPassive={ isEditing }>
				<Meta />
			</Area>
			<Area area="carrousel" isPassive={ isEditing }>
				<Carrousel />
			</Area>
			<Area area="addpage" isPassive={ isEditing }>
				<AddPage />
			</Area>
		</Background>
	);
}

export default CanvasLayout;
