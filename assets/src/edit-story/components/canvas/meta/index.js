/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { useStory } from '../../../app';
import { createPage } from '../../../elements';
import Switch from '../../switch';
import AddPage from './addpage.svg';
import Delete from './delete.svg';
import Duplicate from './duplicate.svg';

const Box = styled.div`
	height: 100%;
	background-color: ${ ( { theme } ) => theme.colors.bg.v1 };
	display: flex;
	flex-direction: row;
	align-items: center;
	justify-content: space-between;
`;

const PageCount = styled.div`
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
`;

const Options = styled.div`
	height: 28px;
	display: flex;
	flex-direction: row;
	align-items: center;
	color: ${ ( { theme } ) => theme.colors.fg.v2 };
	padding: 0 0.5em;
`;

const Divider = styled.span`
	background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	opacity: .3;
	height: 100%;
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
	const {
		state: { currentPageNumber, currentPageId },
		actions: { addPage, deletePage },
	} = useStory();

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
