/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { useStory } from '../../../app';
import DropZone from '../../../components/dropzone';

const List = styled.nav`
	display: flex;
	flex-direction: row;
	align-items: flex-start;
	justify-content: center;
	height: 100%;
	padding-top: 1em;
`;

const Page = styled.a`
	background-color: ${ ( { isActive, theme } ) => isActive ? theme.colors.fg.v1 : theme.colors.mg.v1 };
	height: 48px;
	width: 27px;
	margin: 0 5px;
	cursor: pointer;

	&:hover {
		background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	}
`;

function Canvas() {
	const { state: { pages, currentPageIndex }, actions: { setCurrentPageByIndex, arrangePage } } = useStory();
	return (
		<List>
			{ pages.map( ( page, index ) => {
				const onDrop = ( evt ) => {
					const data = JSON.parse( evt.dataTransfer.getData( 'text' ) );
					if ( ! data || 'page' !== data.type ) {
						return;
					}
					arrangePage( data.index, index );
				};
				// @todo Create a Draggable component for setting data and setting "draggable".
				const onDragStart = ( evt ) => {
					const pageData = {
						type: 'page',
						index,
					};
					evt.dataTransfer.setData( 'text', JSON.stringify( pageData ) );
				};
				return (
					<DropZone onDrop={ onDrop } >
						<Page draggable="true" onDragStart={ onDragStart } key={ index } onClick={ () => setCurrentPageByIndex( index ) } isActive={ index === currentPageIndex } />
					</DropZone>
				);
			} ) }
		</List>
	);
}

export default Canvas;
