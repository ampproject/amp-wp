/**
 * Returns an action object in signalling that a given item is now animated.
 *
 * @param {string}  page        ID of the page the item is in.
 * @param {string}  item        ID of the animated item.
 * @param {?string} predecessor Optional. ID of the item's predecessor in the animation order.
 *
 * @return {Object} Action object.
 */
export function addAnimation( page, item, predecessor ) {
	return {
		type: 'ADD_ANIMATION',
		page,
		item,
		predecessor,
	};
}

/**
 * Returns an action object in signalling that an item's animation type has changed.
 *
 * @param {string} page          ID of the page the item is in.
 * @param {string} item          ID of the animated item.
 * @param {string} animationType Animation type value.
 *
 * @return {Object} Action object.
 */
export function changeAnimationType( page, item, animationType ) {
	return {
		type: 'CHANGE_ANIMATION_TYPE',
		page,
		item,
		animationType,
	};
}

/**
 * Returns an action object in signalling that an item's animation duration has changed.
 *
 * @param {string} page     ID of the page the item is in.
 * @param {string} item     ID of the animated item.
 * @param {number} duration Animation delay value
 *
 * @return {Object} Action object.
 */
export function changeAnimationDuration( page, item, duration ) {
	return {
		type: 'CHANGE_ANIMATION_DURATION',
		page,
		item,
		duration,
	};
}

/**
 * Returns an action object in signalling that an item's animation delay has changed.
 *
 * @param {string} page  ID of the page the item is in.
 * @param {string} item  ID of the animated item.
 * @param {number} delay Animation delay value
 *
 * @return {Object} Action object.
 */
export function changeAnimationDelay( page, item, delay ) {
	return {
		type: 'CHANGE_ANIMATION_DELAY',
		page,
		item,
		delay,
	};
}

/**
 * Returns an action object in signalling that the currently selected page has changed.
 *
 * Only a single page can be edited at a time.
 *
 * @param {string} page ID of the selected page.
 *
 * @return {Object} Action object.
 */
export function setCurrentPage( page ) {
	return {
		type: 'SET_CURRENT_PAGE',
		page,
	};
}

/**
 * Returns an action object in signalling that reorder mode should be initiated.
 *
 * @return {Object} Action object.
 */
export function startReordering() {
	return {
		type: 'START_REORDERING',
	};
}

/**
 * Returns an action object in signalling that a page should be moved within the collection.
 *
 * @param {string} page ID of the moved page.
 * @param {number} index New index.
 *
 * @return {Object} Action object.
 */
export function movePageToPosition( page, index ) {
	return {
		type: 'MOVE_PAGE',
		page,
		index,
	};
}

/**
 * Returns an action object in signalling that the changed page order should be saved.
 *
 * @return {Object} Action object.
 */
export function saveOrder() {
	return {
		type: 'STOP_REORDERING',
	};
}

/**
 * Returns an action object in signalling that the customized order should be reverted.
 *
 * @return {Object} Action object.
 */
export function resetOrder() {
	return {
		type: 'RESET_ORDER',
	};
}
