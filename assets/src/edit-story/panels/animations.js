/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';
import uuid from 'uuid/v4';

/**
 * WordPress dependencies
 */
import { useRef, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { DesignMode, useStory } from '../app';
import AnimationPlayer from '../components/animations/animationPlayer';
import getAnimation from '../components/animations/animationPresets';
import useDelayEffect from '../utils/useDelayEffect';
import { ActionButton, Panel, Title, getCommonValue } from './shared';

const Delete = styled.a`
  cursor: pointer;
`;

function AnimationsPanel( { selectedElements, onSetProperties } ) {
	const animations = getCommonValue( selectedElements, 'animations' ) || [];

	const { state: { designMode }, actions: { setDesignMode } } = useStory();

	const playerRef = useRef( null );
	const [ playState, setPlayState ] = useState( null );

	// An effect for designMode change.
	useDelayEffect(
		() => {
			if ( designMode !== DesignMode.REPLAY || animations.length === 0 ) {
				return null;
			}

			// When we enter a "replay" mode and we have an animation pending.
			const player = new AnimationPlayer( animations.map( ( { id, ...rest } ) => {
				// @todo: a smarter way to lookup nodes. At least limitted to the
				// selected elements.
				const target = document.querySelector( `[data-animation-id="${ id }"]` );
				const { keyframes, timing } = getAnimation( rest );
				return { target, keyframes, timing };
			} ) );
			playerRef.current = player;
			player.onPlayStateChange = () => {
				if ( player.playState === 'finished' || player.playState === 'canceled' ) {
					// Reset everything.
					playerRef.current = null;
					setPlayState( null );
					setDesignMode( DesignMode.DESIGN );
				} else {
					setPlayState( player.playState );
				}
			};
			player.play();
			return () => {
				// Interrupt animation when the editor exits "replay" mode for any
				// reason.
				player.cancel();
			};
		},
		[ designMode, animations, setPlayState ] );

	const handleAddAnimation = () => {
		// @todo: construct animation based on the selected preset from the dropdown
		// in the side-panel.
		const animation = {
			id: uuid(),
			preset: animations.length === 0 ? 'fade-in' : 'fly-in-top',
			timing: {
				duration: 1000,
				iterations: 1,
			},
		};
		onSetProperties( {
			animations: animations.concat( animation ),
		} );
	};

	const handleDeleteAnimation = ( toDelete ) => {
		onSetProperties( {
			animations: animations.filter( ( animation ) => animation.id !== toDelete.id ),
		} );
	};

	const handlePlayAnimation = () => {
		const player = playerRef.current;
		if ( player && playState === 'paused' ) {
			player.play();
		} else {
			// Switch to replay mode. The animation will start automatically.
			setDesignMode( DesignMode.REPLAY );
		}
	};

	const handlePauseAnimation = () => {
		const player = playerRef.current;
		if ( player ) {
			player.pause();
		}
	};

	const handleStopAnimation = () => {
		const player = playerRef.current;
		if ( player ) {
			player.cancel();
		}
	};

	return (
		<Panel onSubmit={ ( event ) => event.preventDefault() }>
			<Title>
				{ 'Animations' }
			</Title>

			<div>
				{ animations.map( ( animation, index ) => (
					<div key={ animation.id } >
						{ `Animation ${ index + 1 }` }
						<Delete onClick={ () => handleDeleteAnimation( animation ) }>
							{ 'Delete' }
						</Delete>
					</div>
				) ) }
			</div>

			<ActionButton onClick={ handleAddAnimation }>
				{ 'Add animation' }
			</ActionButton>

			{ animations.length !== 0 && playState !== 'running' && (
				<ActionButton onClick={ handlePlayAnimation }>
					{ 'Play' }
				</ActionButton>
			) }

			{ playState === 'running' && (
				<ActionButton onClick={ handlePauseAnimation }>
					{ 'Pause' }
				</ActionButton>
			) }

			{ playState !== null && (
				<ActionButton onClick={ handleStopAnimation }>
					{ 'Stop' }
				</ActionButton>
			) }
		</Panel>
	);
}

AnimationsPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default AnimationsPanel;
