/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { useStory } from '../../../app';
import Switch from '../../switch';
import Delete from './delete.svg';
import Duplicate from './duplicate.svg';

const Box = styled.div`
	display: flex;
	align-items: flex-end;
	justify-content: space-between;
	height: 100%;
	padding-bottom: 1em;
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
	const { state: { currentPageNumber }, actions: { deleteCurrentPage } } = useStory();

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
				<Icon onClick={ deleteCurrentPage }>
					<Delete />
				</Icon>
				<Space />
				<Icon>
					<Duplicate />
				</Icon>
			</Options>
		</Box>
	);
}

export default Canvas;
