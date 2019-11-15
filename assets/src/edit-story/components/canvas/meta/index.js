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
	margin-left: 1em;
	opacity: .3;
	height: 24px;
	width: 1px;
`;

const Icon = styled.a`
	margin-left: 1em;
	cursor: pointer;
	color: ${ ( { theme } ) => theme.colors.fg.v4 };

	&:hover {
		color: ${ ( { theme } ) => theme.colors.fg.v1 };
	}
`;

function Canvas() {
	const { state: { currentPageNumber } } = useStory();
	return (
		<Box>
			<PageCount>
				{ `Page ${ currentPageNumber }` }
			</PageCount>
			<Options>
				<Switch label="Helper" />
				<Divider />
				<Icon>
					<Delete />
				</Icon>
				<Icon>
					<Duplicate />
				</Icon>
			</Options>
		</Box>
	);
}

export default Canvas;
