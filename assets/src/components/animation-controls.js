/**
 * WordPress dependencies
 */
import { RangeControl, SelectControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ANIMATION_DURATION_DEFAULTS, AMP_ANIMATION_TYPE_OPTIONS } from '../stories-editor/constants';
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
	const DEFAULT_ANIMATION_DURATION = ANIMATION_DURATION_DEFAULTS[ animationType ] || 0;

	return (
		<Fragment>
			<SelectControl
				label={ __( 'Animation Type', 'amp' ) }
				value={ animationType }
				options={ AMP_ANIMATION_TYPE_OPTIONS }
				onChange={ ( value ) => {
					onAnimationTypeChange( value );

					// Also update these values as these can change per type.
					onAnimationDurationChange( ANIMATION_DURATION_DEFAULTS[ value ] || 0 );
					onAnimationDelayChange( 0 );
				} }
			/>
			{ animationType && (
				<Fragment>
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
				</Fragment>
			) }
		</Fragment>
	);
}

