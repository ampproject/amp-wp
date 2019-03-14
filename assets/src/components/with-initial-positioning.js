/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { createBlock } from '@wordpress/blocks';

/**
 * External dependencies
 */
import { every } from 'lodash';

/**
 * Internal dependencies
 */
import { ALLOWED_CHILD_BLOCKS } from '../constants';
import { withParentBlock } from './';

const positionTopLimit = 75;

export default createHigherOrderComponent(
	( BlockEdit ) => {
		return withParentBlock( ( props ) => {
			const { attributes, name, parentBlock } = props;

			if ( -1 === ALLOWED_CHILD_BLOCKS.indexOf( name ) || ! parentBlock || 'amp/amp-story-page' !== parentBlock.name ) {
				return <BlockEdit { ...props } />;
			}

			// If the positions are not set and if the block is selected.
			if (
				! props.isSelected ||
				0 !== attributes.positionTop ||
				0 !== attributes.positionLeft ||
				! parentBlock.innerBlocks.length
			) {
				return <BlockEdit { ...props } />;
			}

			// Check if it's a new block.
			const newBlock = createBlock( name );
			const isUnmodified = every( newBlock.attributes, ( value, key ) =>
				value === attributes[ key ]
			);

			if ( isUnmodified ) {
				let highestTop = 0;
				// Get all child blocks of the parent block and get the highest "positionTop" value.
				parentBlock.innerBlocks.forEach( ( childBlock ) => {
					if ( childBlock.attributes.positionTop > highestTop ) {
						highestTop = childBlock.attributes.positionTop;
					}
				} );
				// If it's more than the limit, set the new one to 10
				const newPositionTop = highestTop > positionTopLimit ? 10 : highestTop + 10;

				props.setAttributes( {
					positionTop: newPositionTop,
				} );
			}

			return (
				<BlockEdit { ...props } />
			);
		} );
	},
	'withInitialPositioning'
);
