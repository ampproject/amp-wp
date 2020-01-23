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
import { getDefinitionForType } from '../../elements';
import { ElementWithPosition, ElementWithSize, ElementWithRotation } from '../../elements/shared';
import StoryPropTypes from '../../types';
import { useUnits } from '../../units';
import useTransformHandler from './useTransformHandler';

const Wrapper = styled.div`
	${ ElementWithPosition }
	${ ElementWithSize }
	${ ElementWithRotation }
	contain: layout paint;
`;

function DisplayElement( { element } ) {
	const { actions: { getBox } } = useUnits();

	const { id, type } = element;
	// eslint-disable-next-line @wordpress/no-unused-vars-before-return
	const { Display } = getDefinitionForType( type );

	const wrapperRef = useRef( null );

	// eslint-disable-next-line @wordpress/no-unused-vars-before-return
	const box = getBox( element );

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

	// QQQQQ
	if ( type !== 'image' ) {
		return null;
	}

	return (
		<Wrapper
			ref={ wrapperRef }
			{ ...box }
		>
			<Display element={ element } box={ box } />
		</Wrapper>
	);
}

DisplayElement.propTypes = {
	element: StoryPropTypes.element.isRequired,
};

export default DisplayElement;
