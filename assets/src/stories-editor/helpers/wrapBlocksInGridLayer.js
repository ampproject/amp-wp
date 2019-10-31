/**
 * External dependencies
 */
import { ReactElement } from 'react';

/**
 * Internal dependencies
 */
import { wrapBlockInGridLayerDeprecations } from '../deprecations/filters';
import isMovableBlock from './isMovableBlock';

/**
 * Wraps all movable blocks in a grid layer and assigns custom attributes as needed.
 *
 * @param {ReactElement} element                  Block element.
 * @param {Object}       blockType                Block type object.
 * @param {Object}       attributes               Block attributes.
 * @param {number}       attributes.positionTop   Top offset in pixel.
 * @param {number}       attributes.positionLeft  Left offset in pixel.
 * @param {number}       attributes.rotationAngle Rotation angle in degrees.
 * @param {number}       attributes.width         Block width in pixels.
 * @param {number}       attributes.height        Block height in pixels.
 *
 * @return {ReactElement} The wrapped element.
 */
const wrapBlocksInGridLayer = ( element, blockType, attributes ) => {
	if ( ! element || ! isMovableBlock( blockType.name ) ) {
		return element;
	}

	if ( attributes.deprecated && wrapBlockInGridLayerDeprecations[ attributes.deprecated ] ) {
		const deprecateWrapBlocksInGridLayer = wrapBlockInGridLayerDeprecations[ attributes.deprecated ];
		if ( 'function' === typeof deprecateWrapBlocksInGridLayer ) {
			return deprecateWrapBlocksInGridLayer( element, blockType, attributes );
		}
	}
	return element;
};

export default wrapBlocksInGridLayer;
