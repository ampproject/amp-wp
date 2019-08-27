/**
 * WordPress dependencies
 */
import {
	visitAdminPage,
	createNewPost,
	getAllBlocks,
	selectBlockByClientId,
} from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	activateExperience,
	clickButton,
	clickButtonByLabel,
	goToPreviousPage,
	deactivateExperience,
	insertBlock,
	searchForBlock,
} from '../../utils';

describe( 'Stories Editor Screen', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	beforeEach( async () => {
		await createNewPost( { postType: 'amp_story' } );
	} );

	it( 'should allow adding Page Attachment', async () => {
		await insertBlock( 'Page attachment' );
		await expect( page ).toMatchElement( '.wp-block[data-type="amp/amp-story-page-attachment"]' );
	} );

	it( 'should not allow adding Page Attachment if Page Attachment is already present', async () => {
		await insertBlock( 'Page attachment' );
		await searchForBlock( 'Page attachment' );

		await expect( page ).toMatchElement( '.block-editor-inserter__no-results' );
	} );

	it( 'should not allow adding Page Attachment if CTA block is already present', async () => {
		await insertBlock( 'Page' );
		await insertBlock( 'Call to Action' );
		await searchForBlock( 'Page attachment' );

		await expect( page ).toMatchElement( '.block-editor-inserter__no-results' );
	} );

	/*it( 'should allow changing the CTA Text', async () => {
	} );

	it( 'should allow changing the Title Text', async () => {
	} );

	it( 'should allow choosing Posts and Pages as content', async () => {
	} );

	it( 'should display the chosen content in Preview', async () => {

	} );*/
} );
