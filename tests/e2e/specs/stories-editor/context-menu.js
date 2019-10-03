/**
 * WordPress dependencies
 */
import {
	createNewPost,
	getAllBlocks,
	selectBlockByClientId,
	pressKeyWithModifier,
} from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	activateExperience,
	clickButton,
	deactivateExperience,
	goToPreviousPage,
	insertBlock,
	removeAllBlocks,
} from '../../utils';

async function makeRightClick( el ) {
	await el.click( {
		button: 'right',
	} );
}

describe( 'Context Menu', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	const BLOCK_SELECTOR = '.wp-block-amp-amp-story-post-author';
	const POPOVER_SELECTOR = '.amp-story-right-click-menu__popover';
	const ACTIVE_PAGE_SELECTOR = '.amp-page-active';

	beforeEach( async () => {
		await createNewPost( { postType: 'amp_story' } );
		await removeAllBlocks();
		await page.waitForSelector( ACTIVE_PAGE_SELECTOR );
		await insertBlock( 'Author' );
		await page.waitForSelector( BLOCK_SELECTOR );
	} );

	describe( 'using Right Click', () => {
		it( 'opens the right click menu with the block actions when clicking on a block', async () => {
			const block = await page.$( BLOCK_SELECTOR );
			await makeRightClick( block );

			expect( page ).toMatchElement( POPOVER_SELECTOR );
			expect( page ).toMatchElement( POPOVER_SELECTOR + ' .right-click-copy' );
			expect( page ).toMatchElement( POPOVER_SELECTOR + ' .right-click-cut' );
			expect( page ).toMatchElement( POPOVER_SELECTOR + ' .right-click-remove' );
			expect( page ).toMatchElement( POPOVER_SELECTOR + ' .right-click-duplicate' );
		} );

		it( 'does not open the menu by clicking on a page', async () => {
			const pageBlock = await page.$( ACTIVE_PAGE_SELECTOR );
			await makeRightClick( pageBlock );

			expect( page ).not.toMatchElement( POPOVER_SELECTOR );
		} );

		it( 'should open right click menu for pasting on a page if a block has been copied previously', async () => {
			const block = await page.$( BLOCK_SELECTOR );
			await makeRightClick( block );

			await clickButton( 'Copy Block' );
			const pageBlock = await page.$( ACTIVE_PAGE_SELECTOR );
			await makeRightClick( pageBlock );

			expect( page ).toMatchElement( POPOVER_SELECTOR + ' .right-click-paste' );
		} );

		it( 'should allow copying and pasting a block', async () => {
			const block = await page.$( BLOCK_SELECTOR );
			await makeRightClick( block );

			await clickButton( 'Copy Block' );

			await removeAllBlocks();
			await page.waitForSelector( ACTIVE_PAGE_SELECTOR );
			const pageBlock = await page.$( ACTIVE_PAGE_SELECTOR );
			await makeRightClick( pageBlock );

			await clickButton( 'Paste' );
			await page.waitForSelector( ACTIVE_PAGE_SELECTOR + ' ' + BLOCK_SELECTOR );
			expect( page ).toMatchElement( ACTIVE_PAGE_SELECTOR + ' ' + BLOCK_SELECTOR );
		} );

		it( 'should allow cutting and pasting a block', async () => {
			const block = await page.$( BLOCK_SELECTOR );
			await makeRightClick( block );

			await clickButton( 'Cut Block' );
			expect( page ).not.toMatchElement( BLOCK_SELECTOR );

			const pageBlock = await page.$( ACTIVE_PAGE_SELECTOR );
			await makeRightClick( pageBlock );

			await clickButton( 'Paste' );

			await page.waitForSelector( BLOCK_SELECTOR );
			expect( page ).toMatchElement( BLOCK_SELECTOR );
		} );

		it( 'should allow duplicating a block', async () => {
			const block = await page.$( BLOCK_SELECTOR );
			await makeRightClick( block );

			await clickButton( 'Duplicate Block' );

			const nodes = await page.$x(
				'//div[contains(@class, "wp-block-amp-amp-story-post-author")]'
			);
			expect( nodes ).toHaveLength( 2 );
		} );

		it( 'should allow removing a block', async () => {
			const block = await page.$( BLOCK_SELECTOR );
			await makeRightClick( block );

			await clickButton( 'Remove Block' );
			expect( page ).not.toMatchElement( BLOCK_SELECTOR );
		} );

		it( 'should not allow pasting disallowed blocks', async () => {
			const firstPageClientId = ( await getAllBlocks() )[ 0 ].clientId;
			await insertBlock( 'Page' );
			await insertBlock( 'Call to Action' );

			const callToActionSelector = '.wp-block-amp-amp-story-cta';
			const ctaBlock = await page.waitForSelector( callToActionSelector );
			await makeRightClick( ctaBlock );

			await clickButton( 'Copy Block' );

			await goToPreviousPage();
			await selectBlockByClientId( firstPageClientId );
			const pageBlock = await page.$( `#block-${ firstPageClientId }` );
			// Wait for transition time 300ms.
			await page.waitFor( 300 );
			await makeRightClick( pageBlock );

			await clickButton( 'Paste' );
			expect( page ).not.toMatchElement( `#block-${ firstPageClientId } ${ callToActionSelector }` );
		} );

		it( 'should not allow duplicate disallowed blocks', async () => {
			await insertBlock( 'Page' );
			await insertBlock( 'Page Attachment' );
			const callToActionSelector = '.wp-block[data-type="amp/amp-story-page-attachment"]';
			const ctaBlock = await page.waitForSelector( callToActionSelector );
			await makeRightClick( ctaBlock );
			const duplicateSelector = 'right-click-duplicate';
			expect( page ).not.toMatchElement( duplicateSelector );
		} );
	} );

	describe( 'using Keyboard', () => {
		it( 'should open the context menu when pressing Shift+F10', async () => {
			await page.$( BLOCK_SELECTOR );
			await pressKeyWithModifier( 'ctrl', 'F10' );

			expect( page ).toMatchElement( POPOVER_SELECTOR );
			expect( page ).toMatchElement( POPOVER_SELECTOR + ' .right-click-copy' );
			expect( page ).toMatchElement( POPOVER_SELECTOR + ' .right-click-cut' );
			expect( page ).toMatchElement( POPOVER_SELECTOR + ' .right-click-remove' );
			expect( page ).toMatchElement( POPOVER_SELECTOR + ' .right-click-duplicate' );
		} );
	} );
} );
