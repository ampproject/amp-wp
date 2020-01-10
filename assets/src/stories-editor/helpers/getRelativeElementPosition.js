/**
 * Returns the block's actual position in relation to the page it's on.
 *
 * @param {Element} blockElement Block element.
 * @param {Element} parentElement The block parent element.
 *
 * @return {{top: number, left: number}} Relative position of the block.
 */
const getRelativeElementPosition = ( blockElement, parentElement ) => {
	const { left: parentLeft, top: parentTop } = parentElement.getBoundingClientRect();
	const { top, right, bottom, left } = blockElement.getBoundingClientRect();

	return {
		top: top - parentTop,
		right: right - parentLeft,
		bottom: bottom - parentTop,
		left: left - parentLeft,
	};
};

export default getRelativeElementPosition;
