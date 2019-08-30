/**
 * Removes all blocks from post to ensure clean state.
 *
 * @return {Promise<void>} Promise.
 */
export async function removeAllBlocks() {
	await page.evaluate( () => {
		const blocks = wp.data.select( 'core/block-editor' ).getBlocks();
		const clientIds = blocks.map( ( block ) => block.clientId );
		wp.data.dispatch( 'core/block-editor' ).removeBlocks( clientIds );
	} );
}
