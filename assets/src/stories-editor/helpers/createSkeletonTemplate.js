/**
 * WordPress dependencies
 */
import { createBlock } from '@wordpress/blocks';

/**
 * Resets a block's attributes except for a few ones relevant for the layout.
 *
 * @param {Object} block Block object.
 * @param {Object} block.attributes Block attributes.
 *
 * @return {Object} Filtered block attributes.
 */
const resetBlockAttributes = ( block ) => {
	const attributes = {};
	const attributesToKeep = [ 'positionTop', 'positionLeft', 'btnPositionTop', 'btnPositionLeft', 'width', 'height', 'tagName', 'align', 'content', 'text', 'value', 'citation', 'autoFontSize', 'rotationAngle' ];

	for ( const key in block.attributes ) {
		if ( block.attributes.hasOwnProperty( key ) && attributesToKeep.includes( key ) ) {
			attributes[ key ] = block.attributes[ key ];
		}
	}

	return attributes;
};

/**
 * Creates a skeleton template from pre-populated template.
 *
 * Basically resets all block attributes back to their defaults.
 *
 * @param {Object}   template             Template block object.
 * @param {string}   template.name        Block name.
 * @param {Object[]} template.innerBlocks List of inner blocks.
 *
 * @return {Object} Skeleton template block.
 */
const createSkeletonTemplate = ( template ) => {
	const innerBlocks = [];

	for ( const innerBlock of template.innerBlocks ) {
		innerBlocks.push( createBlock( innerBlock.name, resetBlockAttributes( innerBlock ) ) );
	}

	return createBlock( template.name, resetBlockAttributes( template ), innerBlocks );
};

export default createSkeletonTemplate;
