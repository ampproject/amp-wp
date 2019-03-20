/**
 * WordPress dependencies
 */
import { RangeControl, SelectControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ANIMATION_DURATION_DEFAULTS, AMP_ANIMATION_TYPE_OPTIONS } from '../constants';
import { AnimationOrderPicker } from './';

/**
 * Animation controls for AMP Story layout blocks'.
 *
 * @return {Component} Controls.
 */
export default function AnimationControls( {
	animatedBlocks,
	onAnimationTypeChange,
	onAnimationDurationChange,
	onAnimationDelayChange,
	onAnimationAfterChange,
	animationType,
	animationDuration,
	animationDelay,
	animationAfter,
} ) {
	const placeHolder = ANIMATION_DURATION_DEFAULTS[ animationType ] || 0;

	return (
		<Fragment>
			<SelectControl
				key="animation"
				label={ __( 'Animation Type', 'amp' ) }
				value={ animationType }
				options={ AMP_ANIMATION_TYPE_OPTIONS }
				onChange={ onAnimationTypeChange }
			/>
			{ animationType && (
				<Fragment>
					<RangeControl
						key="duration"
						label={ __( 'Duration (ms)', 'amp' ) }
						value={ animationDuration }
						onChange={ onAnimationDurationChange }
						min="0"
						max="5000"
						placeholder={ placeHolder }
						initialPosition={ placeHolder }
					/>
					<RangeControl
						key="delay"
						label={ __( 'Delay (ms)', 'amp' ) }
						value={ animationDelay }
						onChange={ onAnimationDelayChange }
						min="0"
						max="5000"
					/>
					<AnimationOrderPicker
						key="order"
						value={ animationAfter }
						options={ animatedBlocks() }
						onChange={ onAnimationAfterChange }
					/>
				</Fragment>
			) }
		</Fragment>
	);
}

