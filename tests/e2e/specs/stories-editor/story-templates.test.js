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
	} );
} );
