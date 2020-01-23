/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { getDefinitionForType } from '../../elements';
import { ElementWithPosition, ElementWithSize, ElementWithRotation, getBox } from '../../elements/shared';
import useTransformHandler from './useTransformHandler';
import useCanvas from './useCanvas';

const Wrapper = styled.div`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
	contain: layout paint;
`;

function DisplayElement( {
	element: {
		id,
		type,
		x,
		y,
		width,
		height,
		rotationAngle,
		isFullbleed,
		...rest
	},
} ) {
	const {
		actions: { dataToEditorX, dataToEditorY },
	} = useCanvas();

	// eslint-disable-next-line @wordpress/no-unused-vars-before-return
	const { Display } = getDefinitionForType( type );

	const wrapperRef = useRef( null );

	const box1 = getBox( { x, y, width, height, rotationAngle, isFullbleed } );
	const box = {
		x: dataToEditorX(box1.x),
		y: dataToEditorY(box1.y),
		width: dataToEditorX(box1.width),
		height: dataToEditorY(box1.height),
		rotationAngle: box1.rotationAngle,
	};
	// eslint-disable-next-line @wordpress/no-unused-vars-before-return
	const props = { ...box, ...rest, id };

	useTransformHandler( id, ( transform ) => {
		const target = wrapperRef.current;
		if ( transform === null ) {
			target.style.transform = '';
		} else {
			const { translate, rotate, resize } = transform;
			target.style.transform = `translate(${ translate[ 0 ] }px, ${ translate[ 1 ] }px) rotate(${ rotate }deg)`;
			if ( resize[ 0 ] !== 0 && resize[ 1 ] !== 0 ) {
				target.style.width = `${ resize[ 0 ] }px`;
				target.style.height = `${ resize[ 1 ] }px`;
			}
		}
	} );

	return (
		<Wrapper
			ref={ wrapperRef }
			{ ...box }
		>
			<Display { ...props } />
		</Wrapper>
	);
}

DisplayElement.propTypes = {
	element: PropTypes.object.isRequired,
};

export default DisplayElement;
