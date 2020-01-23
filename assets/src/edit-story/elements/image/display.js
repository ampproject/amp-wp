/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { ElementFillContent } from '../shared';
import { useTransformHandler } from '../../components/canvas';
import StoryPropTypes from '../../types';
import { ImageWithScale, getImgProps, getImageWithScaleCss } from './util';

const Element = styled.div`
	${ ElementFillContent }
	overflow: hidden;
`;

const Img = styled.img`
	position: absolute;
	${ ImageWithScale }
`;

function ImageDisplay( {
	element: { id, src, origRatio, scale, focalX, focalY },
	box: { width, height },
} ) {
	const imageRef = useRef( null );

	// eslint-disable-next-line @wordpress/no-unused-vars-before-return
	const imgProps = getImgProps( width, height, scale, focalX, focalY, origRatio );

	useTransformHandler( id, ( transform ) => {
		const target = imageRef.current;
		if ( transform === null ) {
			target.style.transform = '';
		} else {
			const { resize } = transform;
			if ( resize[ 0 ] !== 0 && resize[ 1 ] !== 0 ) {
				const newImgProps = getImgProps( resize[ 0 ], resize[ 1 ], scale, focalX, focalY, origRatio );
				target.style.cssText = getImageWithScaleCss( newImgProps );
			}
		}
	} );

	return (
		<Element>
			<Img ref={ imageRef } draggable={ false } src={ src } { ...imgProps } />
		</Element>
	);
}

ImageDisplay.propTypes = {
	element: StoryPropTypes.elements.image.isRequired,
	box: StoryPropTypes.box.isRequired,
};

export default ImageDisplay;
