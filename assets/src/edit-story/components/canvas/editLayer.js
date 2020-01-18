/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { getDefinitionForType } from '../../elements';
import { useStory } from '../../app';
import withOverlay from '../overlay/withOverlay';
import ElementEdit from './elementEdit';
import { Layer, PageArea } from './layout';
import useCanvas from './useCanvas';

const LayerWithGrayout = styled( Layer )`
  background-color: ${ ( { grayout, theme } ) => grayout ? theme.colors.grayout : 'transparent' };
`;

const EditPageArea = withOverlay( styled( PageArea ).attrs( { className: 'container' } )`
  position: relative;
  width: 100%;
  height: 100%;
` );

function EditLayer( {} ) {
	const { state: { currentPage } } = useStory();
	const { state: { editingElement: editingElementId } } = useCanvas();

	const editingElement =
    editingElementId &&
    currentPage &&
    currentPage.elements.find( ( element ) => element.id === editingElementId );

	if ( ! editingElement ) {
		return null;
	}

	const { editModeGrayout } = getDefinitionForType( editingElement.type );

	return (
		<LayerWithGrayout grayout={ editModeGrayout } pointerEvents={ false }>
			<EditPageArea>
				<ElementEdit element={ editingElement } />
			</EditPageArea>
		</LayerWithGrayout>
	);
}

export default EditLayer;
