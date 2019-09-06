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

/**
 * Displays the animation settings for the page block.
 *
 * It allows the user to preview all animations on the current page.
 *
 * @param {Object} props Component props.
 * @param {string} props.clientId Client ID.
 */
const AnimationSettings = ( { clientId } ) => {
	const { playAnimation, stopAnimation } = useDispatch( 'amp/story' );
	const onAnimationStart = () => playAnimation( clientId );
	const onAnimationStop = () => stopAnimation( clientId );
	const isPlayingAnimations = useSelect( ( select ) => {
		const { isPlayingAnimation } = select( 'amp/story' );
		return isPlayingAnimation( clientId );
	} );

	return (
		<PanelBody
			title={ __( 'Animation', 'amp' ) }
		>
			<IconButton
				icon={ isPlayingAnimations ? 'controls-pause' : 'controls-play' }
				className="is-button is-default"
				onClick={ isPlayingAnimations ? onAnimationStop : onAnimationStart }
			>
				{ isPlayingAnimations ? __( 'Stop All Animations', 'amp' ) : __( 'Play All Animations', 'amp' ) }
			</IconButton>
		</PanelBody>
	);
};

AnimationSettings.propTypes = {
	clientId: PropTypes.string.isRequired,
};

export default AnimationSettings;
