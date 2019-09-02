/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	IconButton,
} from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { withSafeTimeout } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import {
	setAnimationTransformProperties,
	playAnimation,
	getTotalAnimationDuration,
	resetAnimationProperties,
} from '../../helpers';

/**
 * Displays the animation settings for the page block.
 *
 * It allows the user to preview all animations on the current page.
 *
 * @param {Object} props Component props.
 * @param {string} props.clientId Client ID.
 * @param {Function} props.setTimeout Safe setTimeout function.
 */
const AnimationSettings = ( { clientId, setTimeout } ) => {
	const { startAnimation } = useDispatch( 'amp/story' );
	const { stopAnimation } = useDispatch( 'amp/story' );

	// So we know when to stop animation playback mode again.
	const totalAnimationDuration = useSelect( ( select ) => {
		const { getAnimatedBlocksPerPage } = select( 'amp/story' );

		return getTotalAnimationDuration( getAnimatedBlocksPerPage( clientId ) );
	} );

	// A list of all animated blocks for preparing animation playback.
	const allAnimatedBlocks = useSelect( ( select ) => {
		const { getBlock } = select( 'core/block-editor' );
		const { getAnimatedBlocksPerPage } = select( 'amp/story' );

		return getAnimatedBlocksPerPage( clientId )
			.filter( ( { animationType } ) => animationType )
			.map( ( { id, animationType } ) => {
				return {
					block: getBlock( id ),
					animationType,
				};
			} );
	} );

	// A list of functions to animate every block who doesn't have a predecessor.
	const initialBlocksToAnimate = useSelect( ( select ) => {
		const { getBlock } = select( 'core/block-editor' );
		const { getAnimationSuccessors } = select( 'amp/story' );

		const getAnimationPlaybackDefinition = ( { id, animationType, duration, delay } ) => {
			return () => {
				playAnimation(
					getBlock( id ),
					animationType,
					duration ? parseInt( duration ) : 0,
					delay ? parseInt( delay ) : 0,
					() => {
						// Animate every successor block.
						const successors = getAnimationSuccessors( clientId, id )
							.filter( ( { animationType: type } ) => type )
							.map( getAnimationPlaybackDefinition );
						successors.forEach( ( play ) => play() );
					}
				);
			};
		};

		return getAnimationSuccessors( clientId, undefined )
			.filter( ( { animationType } ) => animationType )
			.map( getAnimationPlaybackDefinition );
	} );

	if ( ! allAnimatedBlocks ) {
		return null;
	}

	return (
		<PanelBody
			title={ __( 'Animation', 'amp' ) }
		>
			<IconButton
				icon="controls-play"
				className="is-button is-default"
				onClick={ () => {
					startAnimation();
					allAnimatedBlocks.forEach( ( { block, animationType } ) => setAnimationTransformProperties( block, animationType ) );
					initialBlocksToAnimate.forEach( ( play ) => play() );
					setTimeout(
						() => {
							allAnimatedBlocks.forEach( ( { block, animationType } ) => resetAnimationProperties( block, animationType ) );
							stopAnimation();
						},
						totalAnimationDuration
					);
				} }
			>
				{ __( 'Play All Animations', 'amp' ) }
			</IconButton>
		</PanelBody>
	);
};

AnimationSettings.propTypes = {
	clientId: PropTypes.string.isRequired,
	setTimeout: PropTypes.func.isRequired,
};

export default withSafeTimeout( AnimationSettings );
