/**
 * WordPress dependencies
 */
import { createBlock, getBlockAttributes } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import { isMovableBlock } from './';

/**
 * Filters block transformations.
 *
 * Removes prefixed list transformations to prevent automatic transformation.
 *
 * Adds a custom transform for blocks within <amp-story-grid-layer>.
 *
 * @see https://github.com/ampproject/amp-wp/issues/2370
 *
 * @param {Object} settings Settings.
 * @param {string} name     Block name.
 *
 * @return {Object} Settings.
 */
const filterBlockTransforms = ( settings, name ) => {
	if ( ! isMovableBlock( name ) ) {
		return settings;
	}

	const gridWrapperTransform = {
		type: 'raw',
		priority: 20,
		selector: `amp-story-grid-layer[data-block-name="${ name }"]`,
		transform: ( node ) => {
			const innerHTML = node.outerHTML;
			const blockAttributes = getBlockAttributes( name, innerHTML );

			if ( 'amp/amp-story-text' === name ) {
				/*
				 * When there is nothing that matches the content selector (.amp-text-content),
				 * the pasted content lacks the amp-fit-text wrapper and thus ampFitText is false.
				 */
				if ( ! blockAttributes.content ) {
					blockAttributes.content = node.textContent;
					blockAttributes.tagName = node.nodeName;
					blockAttributes.ampFitText = false;
				}
			}

			return createBlock( name, blockAttributes );
		},
	};

	const transforms = settings.transforms ? { ...settings.transforms } : {};
	let fromTransforms = transforms.from ? [ ...transforms.from ] : [];

	if ( 'core/list' === name ) {
		fromTransforms = fromTransforms.filter( ( { type } ) => 'prefix' !== type );
	}

	fromTransforms.push( gridWrapperTransform );

	return {
		...settings,
		transforms: {
			...transforms,
			from: fromTransforms,
		},
	};
};

export default filterBlockTransforms;
