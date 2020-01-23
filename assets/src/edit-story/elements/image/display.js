/**
 * External dependencies
 */
import PropTypes from 'prop-types';
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
import { ImageWithScale, getImgProps, getImageWithScaleCss } from './util';

const Element = styled.div`
	${ ElementFillContent }
	overflow: hidden;
`;

const Img = styled.img`
	position: absolute;
	${ ImageWithScale }
`;

function ImageDisplay( { id, src, origRatio, width, height, scale, focalX, focalY } ) {
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
