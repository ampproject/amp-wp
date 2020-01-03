/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useLayoutEffect, useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { DesignMode, useStory } from '../../app';
import { getDefinitionForType } from '../../elements';

const Wrapper = styled.div``;

const AnimationWrapper = styled.div``;

function Element( {
	isEditing,
	isSelected,
	setNodeForElement,
	handleSelectElement,
	forwardedRef,
	element: {
		id,
		type,
		...rest
	},
} ) {
	const { Display, Edit, Replay } = getDefinitionForType( type );
	const element = useRef();
	const props = { ...rest, id };

	const {
		state: { designMode },
	} = useStory();

	useLayoutEffect( () => {
		setNodeForElement( id, element.current );
	}, [ id, setNodeForElement ] );

	// Replay mode.
	if ( designMode === DesignMode.REPLAY ) {
		// DO NOT SUBMIT: It's critical for this to have a positioned/sized wrapper
		// as defined in the #4018.
		const Comp = Replay || Display;
		const { animations } = rest;
		const AnimationsCountdown = ( { animationIndex } ) => {
			if ( animations && animationIndex < animations.length ) {
				return (
					<AnimationWrapper data-animation-id={ animations[ animationIndex ].id } >
						<AnimationsCountdown animationIndex={ animationIndex + 1 } />
					</AnimationWrapper>
				);
			}
			return ( <Comp { ...props } /> );
		};
		AnimationsCountdown.propTypes = {
			animationIndex: PropTypes.number.isRequired,
		};
		return (
			<Wrapper>
				<AnimationsCountdown animationIndex={ 0 } />
			</Wrapper>
		);
	}

	// Are we editing this element, display this as Edit component.
	if ( isEditing ) {
		return (
			<Wrapper
				ref={ element }
			>
				<Edit { ...props } />
			</Wrapper>
		);
	}

	return (
		<Wrapper
			onClick={ ( evt ) => handleSelectElement( id, evt ) }
			ref={ element }
		>
			<Display
				{ ...props }
				onPointerDown={ ( evt ) => {
					if ( ! isSelected ) {
						handleSelectElement( id, evt );
					}
				} }
				forwardedRef={ forwardedRef }
			/>
		</Wrapper>
	);
}

Element.propTypes = {
	isEditing: PropTypes.bool.isRequired,
	isSelected: PropTypes.bool.isRequired,
	setNodeForElement: PropTypes.func.isRequired,
	handleSelectElement: PropTypes.func.isRequired,
	element: PropTypes.object.isRequired,
	forwardedRef: PropTypes.oneOfType( [
		PropTypes.object,
		PropTypes.func,
	] ),
};

export default Element;
