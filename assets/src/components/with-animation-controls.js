/**
 * WordPress dependencies
 */
import { createHigherOrderComponent, compose } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
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
	const { getAnimatedBlocks, isValidAnimationPredecessor } = select( 'amp/story' );

	const currentBlock = getSelectedBlockClientId();
	const page = getBlockRootClientId( currentBlock );

	const animatedBlocks = getAnimatedBlocks()[ page ] || [];
	const animationOrderEntry = animatedBlocks.find( ( { id } ) => id === props.clientId );

	return {
		parentBlock: getBlock( getBlockRootClientId( props.clientId ) ),
		// Use parent's clientId instead of anchor attribute.
		// The attribute will be updated via subscribers.
		animationAfter: animationOrderEntry ? animationOrderEntry.parent : undefined,
		getAnimatedBlocks() {
			return ( getAnimatedBlocks()[ page ] || [] )
				.filter( ( { id } ) => id !== currentBlock )
				.filter( ( { id } ) => {
					const block = getBlock( id );

					return block && block.attributes.ampAnimationType && isValidAnimationPredecessor( page, currentBlock, id );
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
		changeAnimationType,
		changeAnimationDuration,
		changeAnimationDelay,
	} = dispatch( 'amp/story' );

	return {
		onAnimationTypeChange( type ) {
			changeAnimationType( page, item, type );
		},
		onAnimationOrderChange( predecessor ) {
			addAnimation( page, item, predecessor );
		},
		onAnimationDurationChange( value ) {
			changeAnimationDuration( page, item, value );
		},
		onAnimationDelayChange( value ) {
			changeAnimationDelay( page, item, value );
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
			const {
				attributes,
				name,
				parentBlock,
				onAnimationTypeChange,
				onAnimationOrderChange,
				onAnimationDurationChange,
				onAnimationDelayChange,
				getAnimatedBlocks,
				animationAfter,
			} = props;

			const { ampAnimationType, ampAnimationDuration, ampAnimationDelay } = attributes;

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
								onAnimationTypeChange={ onAnimationTypeChange }
								onAnimationDurationChange={ onAnimationDurationChange }
								onAnimationDelayChange={ onAnimationDelayChange }
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
