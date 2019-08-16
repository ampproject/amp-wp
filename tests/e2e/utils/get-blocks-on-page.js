/**
 * Internal dependencies
 */
import { wpDataSelect } from './wp-data-select';

/**
 * Returns an array with all blocks on the currently active page.
 *
 * @return {Promise} Promise resolving with an array containing all blocks on the current page.
 */
export async function getBlocksOnPage() {
	const currentPage = await wpDataSelect( 'amp/story', 'getCurrentPage' );
	return wpDataSelect( 'core/block-editor', 'getBlocks', currentPage );
}
