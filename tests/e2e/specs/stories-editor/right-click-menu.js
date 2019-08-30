/**
 * WordPress dependencies
 */
import { createNewPost } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	activateExperience,
	clickButton,
	deactivateExperience,
	insertBlock,
	removeAllBlocks,
} from '../../utils';

describe( 'Code Editor', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	const BLOCK_SELECTOR = '.wp-block-amp-amp-story-post-author';
	const POPOVER_SELECTOR = '.amp-story-right-click-menu__popover';

	beforeEach( async () => {
		await createNewPost( { postType: 'amp_story' } );
		await removeAllBlocks();
		await insertBlock( 'Author' );
		await page.waitForSelector( BLOCK_SELECTOR );
	} );

	it( 'opens the right click menu with the block actions when clicking on a block', async () => {
		const block = await page.$( BLOCK_SELECTOR );
		await block.click( {
			button: 'right',
		} );

		expect( page ).toMatchElement( POPOVER_SELECTOR );
		expect( page ).toMatchElement( POPOVER_SELECTOR + ' .right-click-copy' );
		expect( page ).toMatchElement( POPOVER_SELECTOR + ' .right-click-cut' );
		expect( page ).toMatchElement( POPOVER_SELECTOR + ' .right-click-remove' );
		expect( page ).toMatchElement( POPOVER_SELECTOR + ' .right-click-duplicate' );
	} );

	it( 'does not open the menu by clicking on a page', async () => {
		const pageBlock = await page.$( '.amp-page-active' );
		await pageBlock.click( {
			button: 'right',
		} );
		expect( page ).not.toMatchElement( POPOVER_SELECTOR );
	} );

	/*it( 'should open right click menu for pasting on a page if a block has been copied previously', async () => {
		const block = await page.$( BLOCK_SELECTOR );
		await block.click( {
			button: 'right',
		} );
		await clickButton( 'Copy Block' );
		await page.waitFor( 1500 );
		const pageBlock = await page.$( '.amp-page-active' );
		await page.waitFor( 1500 );
		await pageBlock.click( {
			button: 'right',
		} );
		await page.waitFor( 1500 );
		expect( page ).toMatchElement( POPOVER_SELECTOR + ' .right-click-copy' );
		await page.waitFor( 1500 );
	} );

	it( 'should allow copying and pasting a block', async () => {
		const block = await page.$( BLOCK_SELECTOR );
		await block.click( {
			button: 'right',
		} );
		await clickButton( 'Copy Block' );

		await insertBlock( 'Page' );
		await page.waitForSelector( '.amp-page-active' );
		const pageBlock = await page.$( '.amp-page-active' );
		await pageBlock.click( {
			button: 'right',
		} );
		await clickButton( 'Paste' );

		expect( page ).toMatchElement( '.amp-page-active ' + BLOCK_SELECTOR );
	} );

	it( 'should allow cutting and pasting a block', async () => {
	} );

	it( 'should allow duplicating a block', async () => {
	} );

	it( 'should allow removing a block', async () => {
	} );

	it( 'should close the menu when clicking anywhere outside of the menu', async () => {
	} );

	it( 'should not allow pastin disallowed blocks', async () => {
	} );*/
} );
