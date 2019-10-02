/**
 * Internal dependencies
 */
import { ANIMATION_STATUS } from './constants';

/**
 * Returns a list of all pages and their animated child blocks.
 *
 * @param {Object} state Editor state.
 *
 * @return {Array} Animation order.
 */
export function getAnimatedBlocks( state ) {
	return state.animations && state.animations.animationOrder ? state.animations.animationOrder : {};
}

/**
 * Returns a list of animated blocks for a given page.
 *
 * @param {Object} state Editor state.
 * @param {string} page Page ID.
 *
 * @return {Array} Animation entries.
 */
export function getAnimatedBlocksPerPage( state, page ) {
	return getAnimatedBlocks( state ) && getAnimatedBlocks( state )[ page ] ?
		state.animations.animationOrder[ page ] :
		[];
}

/**
 * Returns a the animation entry for a given item.
 *
 * @param {Object} state Editor state.
 * @param {string} page Page ID.
 * @param {string} item Item ID.
 *
 * @return {Array} Animation entry.
 */
export function getAnimationEntry( state, page, item ) {
	return getAnimatedBlocksPerPage( state, page ).find( ( { id } ) => id === item );
}

/**
 * Returns a list of animated blocks that start directly after a given block.
 *
 * @param {Object} state Editor state.
 * @param {string} page Page ID.
 * @param {string} predecessor Predecessor ID.
 *
 * @return {Array} Animation successors.
 */
export function getAnimationSuccessors( state, page, predecessor ) {
	return getAnimatedBlocksPerPage( state, page ).filter( ( { parent } ) => parent === predecessor );
}

/**
 * Returns whether an animation is currently playing or not.
 *
 * @param {Object} state Editor state.
 * @param {string} [page] Optional. Page ID. If not passed, checks whether any animation is playing anywhere.
 * @param {string} [item] Optional. Item ID. If not passed, checks whether any animation is playing on the given page.
 *
 * @return {boolean} Whether an animation is currently playing.
 */
export function isPlayingAnimation( state, page, item ) {
	if ( page && item ) {
		const entry = getAnimationEntry( state, page, item );
		return entry && entry.status ? entry.status !== ANIMATION_STATUS.stopped : false;
	} else if ( page ) {
		return Boolean( getAnimatedBlocksPerPage( state, page ).find( ( { status } ) => status && status !== ANIMATION_STATUS.stopped ) );
	}

	for ( const p of Object.keys( getAnimatedBlocks( state ) ) ) {
		if ( isPlayingAnimation( state, p ) ) {
			return true;
		}
	}

	return false;
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
	state.animations = state.animations || {};
	state.animations.animationOrder = state.animations.animationOrder || {};
	if ( undefined === predecessor ) {
		return true;
	}

	const pageAnimationOrder = state.animations.animationOrder[ page ] || [];

	const findEntry = ( entryId ) => pageAnimationOrder.find( ( { id } ) => id === entryId );

	const predecessorEntry = findEntry( predecessor );

	if ( ! predecessorEntry ) {
		return false;
	}

	const hasCycle = ( a, b ) => {
		let parent = b;

		while ( parent !== undefined ) {
			if ( parent === a ) {
				return true;
			}

			const parentItem = findEntry( parent );
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

/**
 * Returns copied markup for pasting workaround.
 *
 * @param {Object} state Editor state.
 * @return {string} Markup.
 */
export function getCopiedMarkup( state ) {
	return state.copiedMarkup || '';
}

/**
 * Returns the stories editor settings.
 *
 * @param {Object} state Editor state.
 *
 * @return {Object} The editor settings object.
 */
export function getSettings( state ) {
	return state.editorSettings || window.ampStoriesEditorSettings || {};
}
