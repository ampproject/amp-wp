/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { ReactElement } from 'react';

/**
 * WordPress dependencies
 */
import { RangeControl, SelectControl, IconButton } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withDispatch, withSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ANIMATION_DURATION_DEFAULTS, AMP_ANIMATION_TYPE_OPTIONS } from '../../constants';
import stopIcon from '../../../../images/stories-editor/stop.svg';
import AnimationOrderPicker from './animation-order-picker';

/**
 * Animation controls for AMP Story layout blocks'.
 *
 * @return {ReactElement} Controls.
 */
const AnimationControls = ( {
	animatedBlocks,
	onAnimationTypeChange,
	onAnimationDurationChange,
	onAnimationDelayChange,
	onAnimationAfterChange,
	animationType,
	animationDuration,
	animationDelay,
	animationAfter,
	onAnimationStart,
	onAnimationStop,
	isPlayingAnimation,
	isImageBlock,
} ) => {
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
	isImageBlock: PropTypes.bool,
	animatedBlocks: PropTypes.func.isRequired,
	onAnimationTypeChange: PropTypes.func.isRequired,
	onAnimationDurationChange: PropTypes.func.isRequired,
	onAnimationDelayChange: PropTypes.func.isRequired,
	onAnimationAfterChange: PropTypes.func.isRequired,
	animationType: PropTypes.string,
	animationDuration: PropTypes.number,
	animationDelay: PropTypes.number,
	animationAfter: PropTypes.string,
	onAnimationStart: PropTypes.func.isRequired,
	onAnimationStop: PropTypes.func.isRequired,
	isPlayingAnimation: PropTypes.bool.isRequired,
};

const applyWithSelect = withSelect( ( select, { clientId, page } ) => {
	const { getBlock } = select( 'core/block-editor' );
	const { isPlayingAnimation } = select( 'amp/story' );

	const block = getBlock( clientId );
	const isImageBlock = block && 'core/image' === block.name;

	return {
		isImageBlock,
		isPlayingAnimation: isPlayingAnimation( page, clientId ),
	};
} );

const withAnimationPlayback = withDispatch( ( dispatch, { clientId, page } ) => {
	const { playAnimation, stopAnimation } = dispatch( 'amp/story' );

	return {
		onAnimationStart: () => playAnimation( page, clientId ),
		onAnimationStop: () => stopAnimation( page, clientId ),
	};
} );

const enhance = compose(
	applyWithSelect,
	withAnimationPlayback,
);

export default enhance( AnimationControls );
