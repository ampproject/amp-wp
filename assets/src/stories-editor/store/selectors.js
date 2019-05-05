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
 * Determines whether a given predecessor -> item combination is valid.
 *
 * Returns false if the predecessor itself is not animated or if it would result in a cycle.
 *
 * @param {Object} state       Editor state.
 * @param {string} page        ID of the page the item is in.
 * @param {string} item        ID of the animated item.
 * @param {string} predecessor ID of the animated item's predecessor.
 *
 * @return {boolean} True if the animation predecessor is valid, false otherwise.
 */
export function isValidAnimationPredecessor( state, page, item, predecessor ) {
	if ( undefined === predecessor || ! state.animations ) {
		return true;
	}

	const pageAnimationOrder = state.animations[ page ] || [];
	const predecessorEntry = pageAnimationOrder.find( ( { id } ) => id === predecessor );

	if ( ! predecessorEntry ) {
		return false;
	}

	const hasCycle = ( a, b ) => {
		let parent = b;

		while ( parent !== undefined ) {
			if ( parent === a ) {
				return true;
			}

			const parentItem = pageAnimationOrder.find( ( { id } ) => id === parent );
			parent = parentItem ? parentItem.parent : undefined;
		}

		return false;
	};

	return ! hasCycle( item, predecessor );
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
