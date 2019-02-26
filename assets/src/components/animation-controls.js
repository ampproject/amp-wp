/**
 * WordPress dependencies
 */
import { RangeControl, SelectControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { withSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ANIMATION_DURATION_DEFAULTS, AMP_ANIMATION_TYPE_OPTIONS } from '../helpers';

/**
 * Animation controls for AMP Story layout blocks'.
 *
 * @param {Function} setAttributes Set Attributes.
 * @param {Object} attributes Props.
 * @param {Array} animatedBlocks List of animated blocks on the same page.
 * @return {Component} Controls.
 */
function AnimationControls( { setAttributes, attributes, animatedBlocks } ) {
	const { ampAnimationType, ampAnimationDuration, ampAnimationDelay, ampAnimationAfter } = attributes;

	const placeHolder = ANIMATION_DURATION_DEFAULTS[ ampAnimationType ] || 0;

	const animationAfterOptions = [
		{
			value: '',
			label: __( 'Immediately', 'amp' ),
		},
	];

	animatedBlocks.map( ( block ) => {
		let label;

		// Todo: Cover more special cases if needed.
		switch ( block.name ) {
			case 'core/image':
				label = sprintf( __( '%1$s (%2$s)', 'amp' ), block.attributes.url.lastIndexOf( '/' ).slice( 0, 20 ), block.clientId );
				break;
			case 'core/paragraph':
				const content = block.originalContent ? block.originalContent.replace( /<[^<>]+>/g, ' ' ).slice( 0, 20 ) : '';

				label = content.length > 0 ? sprintf( __( '%1$s (%2$s)', 'amp' ), content, block.type.title ) : block.type.title;
				break;
			default:
				label = sprintf( __( '%1$s (%2$s)', 'amp' ), block.type.title, block.clientId );
		}

		animationAfterOptions.push(
			{
				value: block.attributes.anchor,
				label,
			}
		);
	} );

	return (
		<Fragment>
			<SelectControl
				key="animation"
				label={ __( 'Animation Type', 'amp' ) }
				value={ ampAnimationType }
				options={ AMP_ANIMATION_TYPE_OPTIONS }
				onChange={ ( value ) => ( setAttributes( { ampAnimationType: value } ) ) }
			/>
			{ ampAnimationType && (
				<Fragment>
					<RangeControl
						key="duration"
						label={ __( 'Duration (ms)', 'amp' ) }
						value={ ampAnimationDuration ? parseInt( ampAnimationDuration ) : '' }
						onChange={ ( value ) => {
							value = value + 'ms';
							setAttributes( { ampAnimationDuration: value } );
						} }
						min="0"
						max="5000"
						placeholder={ placeHolder }
						initialPosition={ placeHolder }
					/>
					<RangeControl
						key="delay"
						label={ __( 'Delay (ms)', 'amp' ) }
						value={ ampAnimationDelay ? parseInt( ampAnimationDelay ) : '' }
						onChange={ ( value ) => {
							value = value + 'ms';
							setAttributes( { ampAnimationDelay: value } );
						} }
						min="0"
						max="5000"
					/>
					<SelectControl
						key="order"
						label={ __( 'Begin after', 'amp' ) }
						value={ ampAnimationAfter }
						options={ animationAfterOptions }
						onChange={ ( value ) => setAttributes( { ampAnimationAfter: value } ) }
					/>
				</Fragment>
			) }
		</Fragment>
	);
}

export default withSelect( ( select ) => {
	const {
		getSelectedBlockClientId,
		getBlockRootClientId,
		getBlockOrder,
		getBlocksByClientId,
	} = select( 'core/editor' );

	const { getBlockType } = select( 'core/blocks' );

	const clientId = getSelectedBlockClientId();
	const rootClientId = getBlockRootClientId( clientId );
	const order = getBlockOrder( rootClientId );

	const animatedBlocks = getBlocksByClientId( order )
		.filter( ( block ) => ( block.clientId !== clientId && block.attributes.ampAnimationType ) )
		.map( ( block ) => {
			return {
				...block,
				type: getBlockType( block.name ),
			};
		} );

	return {
		animatedBlocks,
	};
} )( AnimationControls );
