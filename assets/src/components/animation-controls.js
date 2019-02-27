/**
 * WordPress dependencies
 */
import { RangeControl, SelectControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ANIMATION_DURATION_DEFAULTS, AMP_ANIMATION_TYPE_OPTIONS } from '../helpers';

/**
 * Animation controls for AMP Story layout blocks'.
 *
 * @param {Function} setAttributes Set Attributes.
 * @param {Function} onAnimationOrderChange Animation order change callback
 * @param {Function} onAnimationTypeChange Animation type change callback
 * @param {Object} attributes Props.
 * @param {Function} getAnimatedBlocks Function to retrieve list of animated blocks on the same page.
 * @return {Component} Controls.
 */
function AnimationControls( { setAttributes, attributes, getAnimatedBlocks, onAnimationOrderChange, onAnimationTypeChange } ) {
	const { ampAnimationType, ampAnimationDuration, ampAnimationDelay, ampAnimationAfter } = attributes;

	const placeHolder = ANIMATION_DURATION_DEFAULTS[ ampAnimationType ] || 0;

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
			case 'core/paragraph':
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
				value={ ampAnimationType }
				options={ AMP_ANIMATION_TYPE_OPTIONS }
				onChange={ ( value ) => {
					onAnimationTypeChange( value, ampAnimationAfter );
					setAttributes( { ampAnimationType: value } );
				} }
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
						onChange={ ( value ) => {
							onAnimationOrderChange( value );
						} }
					/>
				</Fragment>
			) }
		</Fragment>
	);
}

const applyWithSelect = withSelect( ( select ) => {
	const {
		getSelectedBlockClientId,
		getBlockRootClientId,
		getBlockOrder,
		getBlocksByClientId,
	} = select( 'core/editor' );

	return {
		getAnimatedBlocks() {
			const { getBlockType } = select( 'core/blocks' );

			const clientId = getSelectedBlockClientId();
			const rootClientId = getBlockRootClientId( clientId );
			const order = getBlockOrder( rootClientId );

			return getBlocksByClientId( order )
				.filter( ( block ) => ( block.clientId !== clientId && block.attributes.ampAnimationType ) )
				.map( ( block ) => {
					return {
						...block,
						type: getBlockType( block.name ),
					};
				} );
		},
	};
} );

const applyWithDispatch = withDispatch( ( dispatch, props, { select } ) => {
	const {
		getSelectedBlockClientId,
		getBlockRootClientId,
	} = select( 'core/editor' );

	const item = getSelectedBlockClientId();
	const page = getBlockRootClientId( item );

	const {
		addAnimation,
		removeAnimation,
	} = dispatch( 'amp/story' );

	return {
		onAnimationTypeChange( type, predecessor ) {
			if ( ! type ) {
				removeAnimation( page, item );
			} else {
				addAnimation( page, item, predecessor );
			}
		},
		onAnimationOrderChange( predecessor ) {
			addAnimation( page, item, predecessor );
		},
	};
} );

export default compose(
	applyWithSelect,
	applyWithDispatch,
)( AnimationControls );
