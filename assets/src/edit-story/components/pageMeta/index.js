/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import { createPage } from '../../elements';
import Switch from '../switch';
import Delete from './delete.svg';
import Duplicate from './duplicate.svg';
import AddPage from './addpage.svg';

const Box = styled.div`
	background-color: ${ ( { theme } ) => theme.colors.bg.v1 };
	display: flex;
	align-items: flex-end;
	justify-content: space-between;
	height: 100%;
	padding-bottom: 1em;
	padding-right: 0.5em;
`;

const PageCount = styled.div`
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
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
	width: 1em;
`;

const Icon = styled.a`
	cursor: pointer;
	color: ${ ( { theme } ) => theme.colors.fg.v4 };

	&:hover {
		color: ${ ( { theme } ) => theme.colors.fg.v1 };
	}
`;

function Canvas() {
	const { state: { currentPageNumber, currentPageId }, actions: { addPage, deletePage } } = useStory();

	return (
		<Box>
			<PageCount>
				{ `Page ${ currentPageNumber }` }
			</PageCount>
			<Options>
				<Switch label="Helper" />
				<Space />
				<Divider />
				<Space />
				<Icon onClick={ () => deletePage( { pageId: currentPageId } ) }>
					<Delete />
				</Icon>
				<Space />
				<Icon>
					<Duplicate />
				</Icon>
				<Space />
				<Icon onClick={ () => addPage( { page: createPage() } ) }>
					<AddPage />
				</Icon>
			</Options>
		</Box>
	);
}

export default Canvas;
