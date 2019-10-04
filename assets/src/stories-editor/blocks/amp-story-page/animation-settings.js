/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { sprintf, __, _n } from '@wordpress/i18n';
import {
	PanelBody,
	IconButton,
} from '@wordpress/components';
import { useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import stopIcon from '../../../../images/stories-editor/stop.svg';

/**
 * Displays the animation settings for the page block.
 *
 * It allows the user to preview all animations on the current page.
 *
 * @param {Object} props Component props.
 * @param {string} props.clientId Client ID.
 */
const AnimationSettings = ( { clientId } ) => {
	const {
		isPlayingAnimation,
		animatedBlocks,
	} = useSelect( ( select ) => {
		const { getBlock } = select( 'core/block-editor' );
		const { getAnimatedBlocksPerPage } = select( 'amp/story' );

		return {
			isPlayingAnimation: select( 'amp/story' ).isPlayingAnimation( clientId ),
			animatedBlocks: getAnimatedBlocksPerPage( clientId ).filter( ( { id, animationType } ) => animationType && getBlock( id ) ),
		};
	}, [ clientId ] );

	const { playAnimation, stopAnimation } = useDispatch( 'amp/story' );

	const onAnimationStart = useCallback(
		() => playAnimation( clientId ),
		[ clientId, playAnimation ]
	);
	const onAnimationStop = useCallback(
		() => stopAnimation( clientId ),
		[ clientId, stopAnimation ]
	);

	if ( ! animatedBlocks.length ) {
		return null;
	}

	const buttonLabel = isPlayingAnimation ?
		__( 'Stop All Animations', 'amp' ) :
		sprintf(
			_n( 'Play %s Animation', 'Play %s Animations', animatedBlocks.length, 'amp' ),
			animatedBlocks.length
		);

	return (
		<PanelBody
			title={ __( 'Animation', 'amp' ) }
		>
			<IconButton
				icon={ isPlayingAnimation ? stopIcon( { width: 20, height: 20 } ) : 'controls-play' }
				className="is-button is-default"
				onClick={ isPlayingAnimation ? onAnimationStop : onAnimationStart }
			>
				{ buttonLabel }
			</IconButton>
		</PanelBody>
	);
};

AnimationSettings.propTypes = {
	clientId: PropTypes.string.isRequired,
};

export default AnimationSettings;
