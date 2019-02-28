/**
 * WordPress dependencies
 */
import { RangeControl, SelectControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ANIMATION_DURATION_DEFAULTS, AMP_ANIMATION_TYPE_OPTIONS } from '../helpers';

/**
 * Animation controls for AMP Story layout blocks'.
 *
 * @return {Component} Controls.
 */
export default function AnimationControls( {
	getAnimatedBlocks,
	onAnimationTypeChange,
	onAnimationDurationChange,
	onAnimationDelayChange,
	onAnimationOrderChange,
	animationType,
	animationDuration,
	animationDelay,
	animationAfter,
} ) {
	const placeHolder = ANIMATION_DURATION_DEFAULTS[ animationType ] || 0;

	const animationAfterOptions = [
		{
			value: '',
			label: __( 'Immediately', 'amp' ),
		},
	];

	getAnimatedBlocks().map( ( block ) => {
		let label;

		// Todo: Cover more special cases if needed.
		switch ( block.name ) {
			case 'core/image':
				label = sprintf( __( '%1$s (%2$s)', 'amp' ), block.attributes.url.lastIndexOf( '/' ).slice( 0, 20 ), block.clientId );
				break;
			case 'amp/amp-story-text':
				const content = block.originalContent ? block.originalContent.replace( /<[^<>]+>/g, ' ' ).slice( 0, 20 ) : '';

				label = content.length > 0 ? sprintf( __( '%1$s (%2$s)', 'amp' ), content, block.type.title ) : block.type.title;
				break;
			default:
				label = sprintf( __( '%1$s (%2$s)', 'amp' ), block.type.title, block.clientId );
		}

		animationAfterOptions.push(
			{
				value: block.clientId,
				label,
			}
		);
	} );

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
					<SelectControl
						key="order"
						label={ __( 'Begin after', 'amp' ) }
						value={ animationAfter }
						options={ animationAfterOptions }
						onChange={ onAnimationOrderChange }
					/>
				</Fragment>
			) }
		</Fragment>
	);
}

