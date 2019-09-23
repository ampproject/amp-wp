/**
 * External dependencies
 */
import { first, last } from 'lodash';

/**
 * WordPress dependencies
 */
import { clickButton, createNewPost, getAllBlocks } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { activateExperience, deactivateExperience, insertBlock } from '../../utils';

const TEXT_BLOCK_SELECTOR = '.wp-block[data-type="amp/amp-story-text"]';
const CODE_BLOCK_SELECTOR = '.wp-block[data-type="core/code"]';
const CODE_BLOCK_NAME = 'core/code';

describe( 'Block Position Controls', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	describe( 'Basic control functionality', () => {
		beforeEach( async () => {
			await createNewPost( { postType: 'amp_story' } );
			await insertBlock( 'Preformatted' );
			await insertBlock( 'Code' );
			await insertBlock( 'Image' );
			await insertBlock( 'Video' );
		} );

		it( 'should send the block forward on clicking that button', async () => {
			// The Video block was the last added, so it should be at the front.
			expect( last( ( await getAllBlocks() )[ 0 ].innerBlocks ).name ).toStrictEqual( 'core/video' );

			// Send the code block forward, but not completely to the front.
			await page.click( CODE_BLOCK_SELECTOR );
			await clickButton( 'Forward' );

			// The code block should be have moved forward in the order, but is not yet at the front, that would be an index of 4.
			expect( ( await getAllBlocks() )[ 0 ].innerBlocks[ 3 ].name ).toStrictEqual( CODE_BLOCK_NAME );
		} );

		it( 'should send the block completely to the front on clicking that button', async () => {
			// Send the block completely to the front.
			await page.click( CODE_BLOCK_SELECTOR );
			await clickButton( 'Front' );

			// The code block should now be at the front, which means it'll be last in getAllBlocks().
			expect( last( ( await getAllBlocks() )[ 0 ].innerBlocks ).name ).toStrictEqual( CODE_BLOCK_NAME );
		} );

		it( 'should send the block backward on clicking that button', async () => {
			// Send the code block forward, but not completely to the front.
			await page.click( CODE_BLOCK_SELECTOR );
			await page.click( '.amp-story-controls-send-backwards' );

			// The code block should be have moved backward in the order, but is not completely at the back, that would be an index of 0.
			expect( ( await getAllBlocks() )[ 0 ].innerBlocks[ 1 ].name ).toStrictEqual( CODE_BLOCK_NAME );
		} );

		it( 'should send the block completely to the back on clicking that button', async () => {
			// Send the block completely to the back.
			await page.click( CODE_BLOCK_SELECTOR );
			await page.click( '.amp-story-controls-send-back' );

			// The code block should now be at the back, which means it'll be first in getAllBlocks().
			expect( first( ( await getAllBlocks() )[ 0 ].innerBlocks ).name ).toStrictEqual( CODE_BLOCK_NAME );
		} );
	} );

	describe( 'Buttons are only present when appropriate', () => {
		beforeEach( async () => {
			await createNewPost( { postType: 'amp_story' } );
		} );

		it( 'should not have the Block Position controls when there is only one block on the page', async () => {
			await expect( page ).not.toMatch( 'Block Position' );
		} );

		it( 'should disable the Forward and Front buttons when the selected block is already at the front', async () => {
			await insertBlock( 'Image' );

			// The 'Block Position' controls should be present.
			expect( page ).toMatch( 'Block Position' );

			await page.waitForSelector( '.amp-story-controls-send-front[aria-disabled="true"]' );
			await page.waitForSelector( '.amp-story-controls-send-forward[aria-disabled="true"]' );

			// Since the selected block is already at the front, the 'Front' and 'Forward' buttons should be disabled.
			expect( page ).toMatchElement( '.amp-story-controls-bring-front[aria-disabled="true"]' );
			expect( page ).toMatchElement( '.amp-story-controls-bring-forward[aria-disabled="true"]' );
		} );

		it( 'should disable the Back and Backward buttons when the selected block is already at the front', async () => {
			await insertBlock( 'Image' );
			await page.click( TEXT_BLOCK_SELECTOR );

			// The 'Block Position' controls should again be present.
			expect( page ).toMatch( 'Block Position' );

			await page.waitForSelector( '.amp-story-controls-send-back[aria-disabled="true"]' );
			await page.waitForSelector( '.amp-story-controls-send-backward[aria-disabled="true"]' );

			// Since the selected block is already at the back, the 'Back' and 'Backward' buttons should be disabled.
			expect( page ).toMatchElement( '.amp-story-controls-send-back[aria-disabled="true"]' );
			expect( page ).toMatchElement( '.amp-story-controls-send-backward[aria-disabled="true"]' );
		} );
	} );
} );
