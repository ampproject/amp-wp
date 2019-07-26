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
	} );
} );
