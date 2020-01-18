/**
 * External dependencies
 */
import PropTypes from 'prop-types';
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

const Element = styled.div`
  ${ ElementFillContent }
`;

function ImageFrame( { id } ) {
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
	id: PropTypes.string.isRequired,
};

export default ImageFrame;
