/**
 * WordPress dependencies
 */
import { createHigherOrderComponent, compose } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/editor';
import { Fragment } from '@wordpress/element';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { withDispatch, withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { ALLOWED_BLOCKS } from '../constants';
import { AnimationControls } from './';

const applyWithSelect = withSelect( ( select, props ) => {
	const { getBlockRootClientId, getBlock } = select( 'core/editor' );

	return {
		parentBlock: getBlock( getBlockRootClientId( props.clientId ) ),
		getAnimatedBlocks() {
			const {
				getSelectedBlockClientId,
				getBlockOrder,
				getBlocksByClientId,
			} = select( 'core/editor' );
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

const wrapperWithSelect = compose(
	applyWithSelect,
	applyWithDispatch,
);

export default createHigherOrderComponent(
	( BlockEdit ) => {
		return wrapperWithSelect( ( props ) => {
			const { attributes, setAttributes, name, parentBlock, onAnimationTypeChange, onAnimationOrderChange, getAnimatedBlocks } = props;

			const { ampAnimationType, ampAnimationDuration, ampAnimationDelay, ampAnimationAfter } = attributes;

			if ( -1 === ALLOWED_BLOCKS.indexOf( name ) || ! parentBlock || 'amp/amp-story-page' !== parentBlock.name ) {
				return <BlockEdit { ...props } />;
			}

			return (
				<Fragment>
					<BlockEdit { ...props } />
					<InspectorControls>
						<PanelBody
							title={ __( 'Animation', 'amp' ) }
						>
							<AnimationControls
								getAnimatedBlocks={ getAnimatedBlocks }
								animationType={ ampAnimationType }
								animationDuration={ ampAnimationDuration ? parseInt( ampAnimationDuration ) : '' }
								animationDelay={ ampAnimationDelay ? parseInt( ampAnimationDelay ) : '' }
								animationAfter={ ampAnimationAfter }
								onAnimationTypeChange={ ( value ) => {
									onAnimationTypeChange( value, ampAnimationAfter );
									setAttributes( { ampAnimationType: value } );
								} }
								onAnimationDurationChange={ ( value ) => {
									value = value + 'ms';
									setAttributes( { ampAnimationDuration: value } );
								} }
								onAnimationDelayChange={ ( value ) => {
									value = value + 'ms';
									setAttributes( { ampAnimationDelay: value } );
								} }
								onAnimationOrderChange={ onAnimationOrderChange }
							/>
						</PanelBody>
					</InspectorControls>
				</Fragment>
			);
		} );
	},
	'withAnimationControls'
);
