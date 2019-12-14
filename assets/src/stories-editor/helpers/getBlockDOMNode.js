/**
 * Given a block client ID, returns the corresponding DOM node for the block,
 * if exists. As much as possible, this helper should be avoided, and used only
 * in cases where isolated behaviors need remote access to a block node.
 *
 * @param {string} clientId Block client ID.
 * @param {Element} scope an optional DOM Element to which the selector should be scoped
 *
 * @return {Element} Block DOM node.
 */
const getBlockDOMNode = ( clientId, scope = document ) => {
	return scope.querySelector( `[data-block="${ clientId }"]` );
};

export default getBlockDOMNode;
