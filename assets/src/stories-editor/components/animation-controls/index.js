/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { ReactElement } from 'react';

/**
 * WordPress dependencies
 */
import { RangeControl, SelectControl, IconButton } from '@wordpress/components';
import { useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import AnimationOrderPicker from './animation-order-picker';
import { ANIMATION_DURATION_DEFAULTS, AMP_ANIMATION_TYPE_OPTIONS } from '../../constants';
import stopIcon from '../../../../images/stories-editor/stop.svg';

/**
 * Animation controls for AMP Story layout blocks'.
 *
 * @return {ReactElement} Controls.
 */
const AnimationControls = ( {
	clientId,
	page,
	animatedBlocks,
	onAnimationTypeChange,
	onAnimationDurationChange,
	onAnimationDelayChange,
	onAnimationAfterChange,
	animationType,
	animationDuration,
	animationDelay,
	animationAfter,
} ) => {
	const {
		isImageBlock,
		isPlayingAnimation,
	} = useSelect( ( select ) => {
		const { getBlock } = select( 'core/block-editor' );

		const block = getBlock( clientId );

		return {
			isImageBlock: block && 'core/image' === block.name,
			isPlayingAnimation: select( 'amp/story' ).isPlayingAnimation( page, clientId ),
		};
	}, [ clientId, page ] );

	const { playAnimation, stopAnimation } = useDispatch( 'amp/story' );

	const onAnimationStart = useCallback(
		() => playAnimation( page, clientId ),
		[ page, clientId, playAnimation ]
	);
	const onAnimationStop = useCallback(
		() => stopAnimation( page, clientId ),
		[ page, clientId, stopAnimation ]
	);

	const DEFAULT_ANIMATION_DURATION = ANIMATION_DURATION_DEFAULTS[ animationType ] || 0;

	// "pan" animations are only really meant for images.
	const animationTypeOptions = AMP_ANIMATION_TYPE_OPTIONS.filter( ( { value } ) => {
		return ! ( value.startsWith( 'pan-' ) && ! isImageBlock );
	} );

	return (
		<>
			<SelectControl
				label={ __( 'Animation Type', 'amp' ) }
				value={ animationType }
				options={ animationTypeOptions }
				onChange={ ( value ) => {
					onAnimationTypeChange( value );

					// Also update these values as these can change per type.
					onAnimationDurationChange( ANIMATION_DURATION_DEFAULTS[ value ] || 0 );
					onAnimationDelayChange( 0 );
				} }
			/>
			{ animationType && (
				<>
					<RangeControl
						label={ __( 'Duration (ms)', 'amp' ) }
						value={ animationDuration }
						onChange={ onAnimationDurationChange }
						min="0"
						max="5000"
						placeholder={ DEFAULT_ANIMATION_DURATION }
						initialPosition={ DEFAULT_ANIMATION_DURATION }
					/>
					<RangeControl
						label={ __( 'Delay (ms)', 'amp' ) }
						value={ animationDelay || 0 }
						onChange={ onAnimationDelayChange }
						min="0"
						max="5000"
					/>
					<AnimationOrderPicker
						value={ animationAfter }
						options={ animatedBlocks() }
						onChange={ onAnimationAfterChange }
					/>
					<IconButton
						icon={ isPlayingAnimation ? stopIcon( { width: 20, height: 20 } ) : 'controls-play' }
						className="is-button is-default"
						onClick={ isPlayingAnimation ? onAnimationStop : onAnimationStart }
					>
						{ isPlayingAnimation ? __( 'Stop Animation', 'amp' ) : __( 'Play Animation', 'amp' ) }
					</IconButton>
				</>
			) }
		</>
	);
};

AnimationControls.propTypes = {
	clientId: PropTypes.string.isRequired,
	page: PropTypes.string.isRequired,
	animatedBlocks: PropTypes.func.isRequired,
	onAnimationTypeChange: PropTypes.func.isRequired,
	onAnimationDurationChange: PropTypes.func.isRequired,
	onAnimationDelayChange: PropTypes.func.isRequired,
	onAnimationAfterChange: PropTypes.func.isRequired,
	animationType: PropTypes.string,
	animationDuration: PropTypes.number,
	animationDelay: PropTypes.number,
	animationAfter: PropTypes.string,
};

export default AnimationControls;
