/**
 * WordPress dependencies
 */
import { RangeControl, SelectControl } from '@wordpress/components';
import { Fragment, renderToString } from '@wordpress/element';
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
 * @return {Component} Controls.
 */
function AnimationControls( { setAttributes, attributes, animatedBlocks } ) {
	const placeHolder = ANIMATION_DURATION_DEFAULTS[ attributes.ampAnimationType ] || 0;

	const animationAfterOptions = [
		{
			value: '',
			label: __( 'Immediately', 'amp' ),
		},
	];

	animatedBlocks.map( ( block ) => {
		let label;

		switch ( block.name ) {
			case 'core/paragraph':
				const content = block.originalContent.replace( /<[^<>]+>/g, ' ' ).slice( 0, 20 );

				label = content.length > 0 ? sprintf( __( '%1$s (%2$s)', 'amp' ), content, block.type.title ) : block.type.title;
				break;
			default:
				label = sprintf( __( '%1$s (%2$s)', 'amp' ), block.type.title, block.clientId );
		}

		animationAfterOptions.push(
			{
				// @todo: Make sure ID attribute always exists.
				value: block.attributes.id || block.clientId,
				label,
			}
		);
	} );

	return (
		<Fragment>
			<SelectControl
				key="animation"
				label={ __( 'Animation Type', 'amp' ) }
				value={ attributes.ampAnimationType }
				options={ AMP_ANIMATION_TYPE_OPTIONS }
				onChange={ ( value ) => ( setAttributes( { ampAnimationType: value } ) ) }
			/>
			<RangeControl
				key="duration"
				label={ __( 'Duration (ms)', 'amp' ) }
				value={ attributes.ampAnimationDuration ? parseInt( attributes.ampAnimationDuration ) : '' }
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
				value={ parseInt( attributes.ampAnimationDelay ) }
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
				value={ attributes.ampAnimationAfter }
				options={ animationAfterOptions }
			/>
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

	const animatedBlocks = getBlocksByClientId( order ).filter( ( block ) => {
		return ( block.clientId !== clientId && block.attributes.ampAnimationType );
	} ).map( ( block ) => {
		return {
			...block,
			type: getBlockType( block.name ),
		};
	} );

	return {
		animatedBlocks,
	};
} )( AnimationControls );
