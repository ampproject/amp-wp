/**
 * Returns a list of all pages and their animated child blocks.
 *
 * @param {Object} state Editor state.
 *
 * @return {Array} Animation order.
 */
export function getAnimatedBlocks( state ) {
	return state.animations || {};
}

/**
 * Returns the currently selected page.
 *
 * @param {Object} state Editor state.
 *
 * @return {string} The current page.
 */
export function getCurrentPage( state ) {
	return state.currentPage;
}

/**
 * Returns the customized block order.
 *
 * @param {Object} state Editor state.
 *
 * @return {Array} Block order.
 */
export function getBlockOrder( state ) {
	return state.blocks.order || [];
}

/**
 * Returns the index of a given page within the customized block order.
 *
 * @param {Object} state Editor state.
 * @param {string} page  ID of the page that should be looked up.
 *
 * @return {number} The page's index.
 */
export function getBlockIndex( state, page ) {
	return state.blocks.order ? state.blocks.order.indexOf( page ) : null;
}

/**
 * Returns whether reordering is currently in progress.
 *
 * @param {Object} state Editor state.
 *
 * @return {boolean} Whether reordering is in progress.
 */
export function isReordering( state ) {
	return state.blocks.isReordering || false;
}
