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
		beforeEach( async () => {
			await createNewPost( { postType: 'amp_story' } );
			// Select the Text block inserted by default.
			await selectBlockByClassName( 'wp-block-amp-story-text' );
		} );

		it( 'it should not resize smaller than the set minimum width and height', async () => {
			const textBlockMinHeight = 30;
			const textBlockMinWidth = 40;
			let height, width;
			const resizableHandleBottom = await page.$( '.wp-block.is-selected .components-resizable-box__handle-bottom' );
			await dragAndResize( resizableHandleBottom, { x: 0, y: -250 } );
			height = await page.evaluate( () => document.querySelector( '.wp-block.is-selected' ).clientHeight );
			expect( height ).toStrictEqual( textBlockMinHeight );

			const resizableHandleTop = await page.$( '.wp-block.is-selected .components-resizable-box__handle-top' );
			await dragAndResize( resizableHandleTop, { x: 0, y: 250 } );
			height = await page.evaluate( () => document.querySelector( '.wp-block.is-selected' ).clientHeight );
			expect( height ).toStrictEqual( textBlockMinHeight );

			const resizableHandleLeft = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left' );
			await dragAndResize( resizableHandleLeft, { x: 300, y: 0 } );
			width = await page.evaluate( () => document.querySelector( '.wp-block.is-selected' ).clientWidth );
			expect( width ).toStrictEqual( textBlockMinWidth );

			const resizableHandleRight = await page.$( '.wp-block.is-selected .components-resizable-box__handle-right' );
			await dragAndResize( resizableHandleRight, { x: -300, y: 0 } );
			width = await page.evaluate( () => document.querySelector( '.wp-block.is-selected' ).clientWidth );
			expect( width ).toStrictEqual( textBlockMinWidth );
		} );
	} );
} );
