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
import { useCanvas } from '../../components/canvas';
import useDoubleClick from '../../utils/useDoubleClick';
import { ElementFillContent } from '../shared';
import StoryPropTypes from '../../types';

const Element = styled.div`
  ${ ElementFillContent }
`;

function ImageFrame( { element: { id } } ) {
	const {
		actions: { setEditingElement },
	} = useCanvas();
	const handleSingleClick = useCallback( () => {}, [] );
	const handleDoubleClick = useCallback( () => setEditingElement( id ), [ id, setEditingElement ] );
	const getHandleClick = useDoubleClick( handleSingleClick, handleDoubleClick );
	return (
		<Element onClick={ getHandleClick( id ) } />
	);
}

ImageFrame.propTypes = {
	element: StoryPropTypes.elements.image.isRequired,
};

export default ImageFrame;
