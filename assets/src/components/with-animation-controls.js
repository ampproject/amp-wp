/**
 * WordPress dependencies
 */
import { createHigherOrderComponent, compose } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/editor';
import { Fragment } from '@wordpress/element';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { withDispatch, withSelect } from '@wordpress/data';
import { getBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import { ALLOWED_CHILD_BLOCKS } from '../constants';
import { AnimationControls } from './';

const applyWithSelect = withSelect( ( select, props ) => {
	const { getSelectedBlockClientId, getBlockRootClientId, getBlock } = select( 'core/editor' );
	const { getAnimationOrder } = select( 'amp/story' );

	const currentBlock = getSelectedBlockClientId();
	const page = getBlockRootClientId( currentBlock );

	const animatedBlocks = getAnimationOrder()[ page ] || [];
	const animationOrderEntry = animatedBlocks.find( ( { id } ) => id === props.clientId );

	return {
		parentBlock: getBlock( getBlockRootClientId( props.clientId ) ),
		animationAfter: animationOrderEntry ? animationOrderEntry.parent : undefined,
		getAnimatedBlocks() {
			return ( getAnimationOrder()[ page ] || [] )
				.filter( ( { id } ) => id !== currentBlock )
				.filter( ( { id } ) => {
					const block = getBlock( id );

					return block && block.attributes.ampAnimationType;
				} )
				.map( ( { id } ) => {
					const block = getBlock( id );
					return {
						value: id,
						label: block.name,
						block,
						blockType: getBlockType( block.name ),
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

/**
 * Higher-order component that adds animation controls to a block.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	( BlockEdit ) => {
		return wrapperWithSelect( ( props ) => {
			const { attributes, setAttributes, name, parentBlock, onAnimationTypeChange, onAnimationOrderChange, getAnimatedBlocks, animationAfter } = props;

			const { ampAnimationType, ampAnimationDuration, ampAnimationDelay, ampAnimationAfter } = attributes;

			if ( -1 === ALLOWED_CHILD_BLOCKS.indexOf( name ) || ! parentBlock || 'amp/amp-story-page' !== parentBlock.name ) {
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
								animatedBlocks={ getAnimatedBlocks }
								animationType={ ampAnimationType }
								animationDuration={ ampAnimationDuration ? parseInt( ampAnimationDuration ) : '' }
								animationDelay={ ampAnimationDelay ? parseInt( ampAnimationDelay ) : '' }
								animationAfter={ animationAfter }
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
								onAnimationAfterChange={ onAnimationOrderChange }
							/>
						</PanelBody>
					</InspectorControls>
				</Fragment>
			);
		} );
	},
	'withAnimationControls'
);
