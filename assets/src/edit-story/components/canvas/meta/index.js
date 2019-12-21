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
import { PAGE_WIDTH } from '../../../constants';
import { useStory, useHistory } from '../../../app';
import Switch from '../../switch';
import Delete from './delete_icon.svg';
import Duplicate from './duplicate_icon.svg';
import Undo from './undo_icon.svg';
import Redo from './redo_icon.svg';

const SIZE = 46;

const Wrapper = styled.div`
	display: flex;
	align-items: flex-start;
	justify-content: center;
`;

const Box = styled.div`
	display: flex;
	align-items: flex-start;
	position: relative;
`;

const BoxContent = styled.div`
	display: flex;
	align-items: center;
	justify-content: space-between;
	height: ${ SIZE }px;
	width: ${ PAGE_WIDTH + SIZE }px;
	background-color: ${ ( { theme } ) => theme.colors.bg.v6 };
	padding: 0 ${ SIZE / 2 }px;
	border-radius: 0 0 ${ SIZE / 2 }px ${ SIZE / 2 }px;
`;

const Corner = styled.span`
	width: ${ SIZE / 2 }px;
	height: ${ SIZE / 2 }px;
	background-color: ${ ( { theme } ) => theme.colors.bg.v6 };
	position: relative;

	order: ${ ( { isStart } ) => isStart ? -1 : 1 };

	&::before {
		content: '';
		display: block;
		position: absolute;
		background-color: ${ ( { theme } ) => theme.colors.bg.v1 };
		width: ${ SIZE / 2 }px;
		height: ${ SIZE / 2 }px;
		top: 0;
		left: 0;

		${ ( { isStart } ) => isStart ? `
			border-radius: 0 ${ SIZE / 2 }px 0 0;
		` : `
			border-radius: ${ SIZE / 2 }px 0 0 0;
		` }
	}
`;

const PageCount = styled.div`
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
	width: 55px;
	font-size: 15px;
`;

const Options = styled.div`
	display: flex;
	align-items: center;
	color: ${ ( { theme } ) => theme.colors.fg.v2 };
`;

const Divider = styled.span`
	background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	opacity: .3;
	height: 24px;
	width: 1px;
`;

const Space = styled.div`
	width: 13px;
`;

const Icon = styled.a`
	cursor: pointer;
	color: ${ ( { theme } ) => theme.colors.fg.v4 };

	&:hover {
		color: ${ ( { theme } ) => theme.colors.fg.v1 };
	}

	${ ( { disabled } ) => disabled && `
		opacity: .3;
		pointer-events: none;
	` }

	svg {
		width: 24px;
		height: 24px;
		display: block;
	}
`;

function Canvas() {
	const { state: { canUndo, canRedo }, actions: { undo, redo } } = useHistory();
	const { state: { currentPageNumber }, actions: { deleteCurrentPage } } = useStory();
	const handleDelete = useCallback( () => {
		deleteCurrentPage();
	}, [ deleteCurrentPage ] );

	if ( currentPageNumber === null ) {
		return null;
	}

	return (
		<Wrapper>
			<Box>
				<Corner isStart />
				<BoxContent>
					<Options>
						<PageCount>
							{ `Page ${ currentPageNumber }:` }
						</PageCount>
						<Space />
						<Icon onClick={ handleDelete }>
							<Delete />
						</Icon>
						<Space />
						<Icon>
							<Duplicate />
						</Icon>
					</Options>
					<Options>
						<Icon disabled={ ! canUndo } onClick={ () => undo() }>
							<Undo />
						</Icon>
						<Space />
						<Icon disabled={ ! canRedo } onClick={ () => redo() }>
							<Redo />
						</Icon>
						<Space />
						<Divider />
						<Space />
						<Switch label="Helper" />
					</Options>
				</BoxContent>
				<Corner />
			</Box>
		</Wrapper>
	);
}

export default Canvas;
