/**
 * Check if block is CTA block.
 *
 * @param {string} blockName Block name.
 * @return {boolean} Boolean if block is / is not a CTA block.
 */
export const isPCTABlock = ( blockName ) => {
	return 'amp/amp-story-cta' === blockName;
};

export default isPCTABlock;
