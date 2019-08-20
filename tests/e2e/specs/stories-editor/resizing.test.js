/**
 * WordPress dependencies
 */
import { createNewPost, selectBlockByClientId, getAllBlocks, dragAndResize } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	activateExperience,
	deactivateExperience,
	selectBlockByClassName,
} from '../../utils';

describe( 'Resizing', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	describe( 'Text block', () => {
		beforeAll( async () => {
			await createNewPost( { postType: 'amp_story' } );
			// Select the Text block inserted by default.
			await selectBlockByClassName( 'wp-block-amp-story-text' );
		} );

		it( 'it should not resize smaller than the set minimum width and height', async () => {
			const resizableHandle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-bottom' );
			await page.waitFor( 4000 );
			await dragAndResize( resizableHandle, { x: 0, y: 50 } );
			await page.waitFor( 4000 );
		} );
	} );
} );
