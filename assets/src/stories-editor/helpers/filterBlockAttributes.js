const blockContentDiv = document.createElement( 'div' );

/**
 * Filters block attributes to make sure that the className is taken even though it's wrapped in a grid layer.
 *
 * @param {Object} blockAttributes Block attributes.
 * @param {Object} blockType       Block type object. Unused.
 * @param {string} innerHTML       Inner HTML from saved content.
 *
 * @return {Object} Block attributes.
 */
const filterBlockAttributes = ( blockAttributes, blockType, innerHTML ) => {
	if ( ! blockAttributes.className && innerHTML.includes( 'is-style-' ) && 0 === innerHTML.indexOf( '<amp-story-grid-layer' ) ) {
		blockContentDiv.innerHTML = innerHTML;

		// Lets check the first child of the amp-story-grid-layer for the className.
		if (
			blockContentDiv.children[ 0 ].children.length &&
			blockContentDiv.children[ 0 ].children[ 0 ].children.length &&
			blockContentDiv.children[ 0 ].children[ 0 ].children[ 0 ].className.includes( 'is-style-' )
		) {
			blockAttributes.className = blockContentDiv.children[ 0 ].children[ 0 ].children[ 0 ].className;
		}
	}

	return blockAttributes;
};

export default filterBlockAttributes;
