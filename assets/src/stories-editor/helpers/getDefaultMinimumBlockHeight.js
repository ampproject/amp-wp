/**
 * Returns the minimum height for a given block.
 *
 * @param {string} name Block name.
 *
 * @return {number} Block height in pixels.
 */
const getDefaultMinimumBlockHeight = ( name ) => {
	switch ( name ) {
		case 'core/quote':
		case 'core/video':
		case 'core/embed':
			return 200;

		case 'core/pullquote':
			return 250;

		case 'core/table':
			return 100;

		case 'amp/amp-story-post-author':
		case 'amp/amp-story-post-date':
			return 50;

		case 'amp/amp-story-post-title':
			return 100;

		default:
			return 60;
	}
};

export default getDefaultMinimumBlockHeight;
