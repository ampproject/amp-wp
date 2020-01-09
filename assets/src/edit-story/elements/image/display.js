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
import { getImgProps, ImageWithScale } from './util';

const Element = styled.div`
	${ ElementFillContent }
	overflow: hidden;
`;

const Img = styled.img`
	position: absolute;
	${ ImageWithScale }
`;

function ImageDisplay( { id, src, origRatio, width, height, scale, focalX, focalY } ) {
	const imgProps = getImgProps( width, height, scale, focalX, focalY, origRatio );
	const {
		actions: { setEditingElement },
	} = useCanvas();
	const handleSingleClick = useCallback( () => {}, [] );
	const handleDoubleClick = useCallback( () => setEditingElement( id ), [ id, setEditingElement ] );
	const getHandleClick = useDoubleClick( handleSingleClick, handleDoubleClick );
	return (
		<Element onClick={ getHandleClick( id ) }>
			<Img draggable={ false } src={ src } { ...imgProps } />
		</Element>
	);
}

ImageDisplay.propTypes = {
	id: PropTypes.string.isRequired,
	src: PropTypes.string.isRequired,
	origRatio: PropTypes.number.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	scale: PropTypes.number,
	focalX: PropTypes.number,
	focalY: PropTypes.number,
};

ImageDisplay.defaultProps = {
	scale: null,
	focalX: null,
	focalY: null,
};

export default ImageDisplay;
