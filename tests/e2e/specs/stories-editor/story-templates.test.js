/**
 * WordPress dependencies
 */
import { visitAdminPage, createNewPost, searchForBlock } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { activateExperience, deactivateExperience, openTemplateInserter, searchForBlock as searchForStoryBlock } from '../../utils';

describe( 'Story Templates', () => {
	describe( 'Stories experience disabled', () => {
		it( 'should hide story templates from the reusable blocks management screen', async () => {
			await visitAdminPage( 'edit.php', 'post_type=wp_block' );

			await expect( page ).toMatchElement( '.no-items' );
		} );

		it( 'should hide story templates in the regular block editor', async () => {
			await createNewPost();
			await searchForBlock( 'Template' );

			await expect( page ).toMatchElement( '.block-editor-inserter__no-results' );
		} );
	} );

	describe( 'Stories experience enabled', () => {
		beforeAll( async () => {
			await activateExperience( 'stories' );
		} );

		afterAll( async () => {
			await deactivateExperience( 'stories' );
		} );

		it( 'should hide story templates from the reusable blocks management screen', async () => {
			await visitAdminPage( 'edit.php', 'post_type=wp_block' );

			await expect( page ).toMatchElement( '.no-items' );
		} );

		it( 'should hide story templates in the regular block editor inserter', async () => {
			await createNewPost();
			await searchForBlock( 'Template' );

			await expect( page ).toMatchElement( '.block-editor-inserter__no-results' );
		} );

		it( 'should hide story templates in the stories editor inserter', async () => {
			await createNewPost( { postType: 'amp_story' } );
			await searchForStoryBlock( 'Template' );

			await expect( page ).toMatchElement( '.block-editor-inserter__no-results' );
		} );

		it( 'should load story templates in the stories editor', async () => {
			await createNewPost( { postType: 'amp_story' } );

			await openTemplateInserter();

			const numberOfTemplates = await page.$$eval( '.block-editor-block-preview', ( templates ) => templates.length );
			expect( numberOfTemplates ).toStrictEqual( 11 ); // 10 default templates plus the empty page.
		} );

		it( 'should insert the correct blocks and as skeletons when clicking on a template', async () => {
			await createNewPost( { postType: 'amp_story' } );

			await openTemplateInserter();

			const nodes = await page.$x(
				'//*[contains(@class,"block-editor-block-preview")]'
			);

			// Click on the template including quote and image.
			await nodes[ 3 ].click();

			const numberOfQuotes = await page.$$eval( '.amp-page-active .wp-block-quote', ( elements ) => elements.length );
			expect( numberOfQuotes ).toStrictEqual( 1 );

			const numberOfImages = await page.$$eval( '.amp-page-active .wp-block-image', ( elements ) => elements.length );
			expect( numberOfImages ).toStrictEqual( 1 );

			// Verify that only 2 child blocks were added.
			const numberOfBlocks = await page.$$eval( '.amp-page-active .wp-block.editor-block-list__block', ( elements ) => elements.length );
			expect( numberOfBlocks ).toStrictEqual( 2 );

			// Verify that the image is added empty, as placeholder.
			const placeholderImages = await page.$$eval( '.amp-page-active .wp-block-image.block-editor-media-placeholder', ( elements ) => elements.length );
			expect( placeholderImages ).toStrictEqual( 1 );

			// Verify that the block is not added with style.
			const defaultStyledQuote = await page.$$eval( '.amp-page-active .wp-block-quote.is-style-white', ( elements ) => elements.length );
			expect( defaultStyledQuote ).toStrictEqual( 0 );
		} );

		it( 'should contain expected content in the template preview', async () => {
			await createNewPost( { postType: 'amp_story' } );

			await openTemplateInserter();

			// Wait until the templates are loaded and blocks accessible.
			await page.waitForSelector( '.block-editor-block-preview .wp-block' );

			const templateContents = await page.evaluate( () => {
				const contents = [];
				const previews = document.querySelectorAll( '.block-editor-block-preview .block-editor-inner-blocks.has-overlay' );
				previews.forEach( function( preview, index ) {
					contents[ index ] = preview.innerHTML;
				} );
				return contents;
			} );

			// Travel Tip template.
			expect( templateContents[ 0 ] ).toContain( 'This template is great for sharing tips' );
			// Quote template.
			expect( templateContents[ 1 ] ).toContain( '<p>Everyone soon or late comes round Rome</p>' );
			// Travel CTA template.
			expect( templateContents[ 2 ] ).toContain( '<strong>Show packing tips</strong>' );
			// Title Page.
			expect( templateContents[ 3 ] ).toContain( 'wp-block-amp-amp-story-post-date has-text-color' );
			// Vertical.
			expect( templateContents[ 4 ] ).toContain( 'Journey into the past' );
			// Fandom Title.
			expect( templateContents[ 5 ] ).toContain( 'SPOILERS ALERT' );
			// Fandom CTA.
			expect( templateContents[ 6 ] ).toContain( '<strong>S</strong>tart Quiz' );
			// Fandom Fact.
			expect( templateContents[ 7 ] ).toContain( 'Robb Start made Jon his heir<br data-rich-text-line-break=\"true\">(in books)' );
			// Fandom Fact Text.
			expect( templateContents[ 8 ] ).toContain( 'One of the biggest things missing from the show is the fact that before his death, Robb Start legitimizes Jon Snow as a Stark and makes him his heir.' );
			// Fandom Intro
			expect( templateContents[ 9 ] ).toContain( 'got-logo.png' );
		} );
	} );
} );
