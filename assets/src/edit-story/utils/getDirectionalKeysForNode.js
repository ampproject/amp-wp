/**
 * Get names of keys to use to navigate forward and backward based on
 * the reading direction of text in the given node.
 *
 * This returns an object with two keys, `backward` and `forward`.
 *
 * If reading direction is LTR, this will return `{forward: 'right', backward: 'left'}`.
 *
 * If reading direction is RTL, this will return `{forward: 'left', backward: 'right'}`.
 *
 * @param {Element} node  DOM element to get directional keys for.
 * @return {Object} An object with keys `forward` and `backward` mapping
 * to `left` and `right` relative to current reading direction.
 */
function getDirectionalKeysForNode( node ) {
	const direction = window.getComputedStyle( node ).direction;
	const isLTR = direction === 'ltr';
	return isLTR ?
		{ forward: 'right', backward: 'left' } :
		{ forward: 'left', backward: 'right' };
}

export default getDirectionalKeysForNode;
