/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { RangeControl, SelectControl, IconButton } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ANIMATION_DURATION_DEFAULTS, AMP_ANIMATION_TYPE_OPTIONS } from '../constants';
import { AnimationOrderPicker } from './';
import withSelectedBlock from './higher-order/with-selected-block';
import { playAnimation } from '../helpers';

/**
 * Animation controls for AMP Story layout blocks'.
 *
 * @return {Component} Controls.
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
	selectedBlock,
	onAnimationStart,
	onAnimationStop,
} ) => {
	const DEFAULT_ANIMATION_DURATION = ANIMATION_DURATION_DEFAULTS[ animationType ] || 0;

	const isImageBlock = selectedBlock && 'core/image' === selectedBlock.name;

	// pan- animations are only really meant for images.
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
						icon="controls-play"
						onClick={ () => {
							onAnimationStart();
							playAnimation( selectedBlock, animationType, animationDuration, animationDelay, onAnimationStop );
						} }
					>
						{ __( 'Play Animation', 'amp' ) }
					</IconButton>
				</>
			) }
		</>
	);
};

AnimationControls.propTypes = {
	animatedBlocks: PropTypes.func.isRequired,
	onAnimationTypeChange: PropTypes.func.isRequired,
	onAnimationDurationChange: PropTypes.func.isRequired,
	onAnimationDelayChange: PropTypes.func.isRequired,
	onAnimationAfterChange: PropTypes.func.isRequired,
	animationType: PropTypes.string,
	animationDuration: PropTypes.oneOfType( [ PropTypes.string, PropTypes.number ] ),
	animationDelay: PropTypes.oneOfType( [ PropTypes.string, PropTypes.number ] ),
	animationAfter: PropTypes.string,
	selectedBlock: PropTypes.object,
	onAnimationStart: PropTypes.func.isRequired,
	onAnimationStop: PropTypes.func.isRequired,
};

const withAnimationPlayback = withDispatch( ( dispatch ) => {
	const { startAnimation, stopAnimation } = dispatch( 'amp/story' );

	return {
		onAnimationStart: startAnimation,
		onAnimationStop: stopAnimation,
	};
} );

const enhance = compose(
	withSelectedBlock,
	withAnimationPlayback
);

export default enhance( AnimationControls );
