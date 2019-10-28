/**
 * WordPress dependencies
 */
import {
	createNewPost,
	saveDraft,
} from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	activateExperience,
	clickButton,
	deactivateExperience,
	insertBlock,
	searchForBlock, openPreviewPage,
} from '../../utils';

/**
 * Opens the first found Page Attachment.
 *
 * @return {Promise<void>} Promise.
 */
async function openPageAttachment() {
	const openAttachmentIconSelector = '.amp-story-page-open-attachment-icon';
	await page.waitForSelector( openAttachmentIconSelector );
	await page.evaluate( ( selector ) => {
		const icon = document.querySelector( selector );
		icon.click();
	}, openAttachmentIconSelector );
}

/**
 * Searches and chooses Page Attachment content by exact title match.
 *
 * @param {string} title Post/page title.
 * @return {Promise<void>} Promise.
 */
async function searchAndChooseAttachment( title ) {
	const searchInput = '.block-editor-post-input input';
	await page.waitForSelector( searchInput );
	await page.type( searchInput, title );
	await clickButton( title );
}

const TITLE_SELECTOR = '.amp-page-attachment-content h2';

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
		await insertBlock( 'Page Attachment' );
		await expect( page ).toMatchElement( '.wp-block[data-type="amp/amp-story-page-attachment"]' );
	} );

	it( 'should not allow adding Page Attachment if Page Attachment is already present', async () => {
		await insertBlock( 'Page Attachment' );
		await searchForBlock( 'Page Attachment' );

		await expect( page ).toMatchElement( '.block-editor-inserter__no-results' );
	} );

	it( 'should not allow adding Page Attachment if CTA block is already present', async () => {
		await insertBlock( 'Page' );
		await insertBlock( 'Call to Action' );
		await searchForBlock( 'Page Attachment' );

		await expect( page ).toMatchElement( '.block-editor-inserter__no-results' );
	} );

	it( 'should allow changing the CTA Text', async () => {
		await insertBlock( 'Page Attachment' );
		const callToActionInputSelector = '.amp-story-page-attachment__text.block-editor-rich-text__editable';
		await page.waitForSelector( callToActionInputSelector );
		await page.type( callToActionInputSelector, 'Hello, ' );

		await saveDraft();
		await page.reload();
		await page.waitForSelector( callToActionInputSelector );

		const content = await page.$eval( callToActionInputSelector, ( node ) => node.textContent );
		const expectedText = 'Hello, Swipe Up';
		await expect( content ).toStrictEqual( expectedText );
	} );

	it( 'should allow changing the Title Text', async () => {
		await insertBlock( 'Page Attachment' );
		await openPageAttachment();

		const titleInputSelector = '.amp-story-page-attachment-title.block-editor-rich-text__editable';
		await page.waitForSelector( titleInputSelector );
		const title = 'Attachment Title';
		await page.type( titleInputSelector, title );

		await saveDraft();
		await page.reload();
		await openPageAttachment();

		const content = await page.$eval( titleInputSelector, ( node ) => node.textContent );
		expect( content ).toStrictEqual( title );
	} );

	it( 'should allow choosing Posts as content', async () => {
		await insertBlock( 'Page Attachment' );
		await openPageAttachment();

		const defaultPostTitle = 'Hello world!';

		await searchAndChooseAttachment( defaultPostTitle );

		await page.waitForSelector( TITLE_SELECTOR );
		const titleInContent = await page.$eval( TITLE_SELECTOR, ( node ) => node.textContent );
		await expect( titleInContent ).toMatch( defaultPostTitle );
	} );

	it( 'should allow choosing Pages as content', async () => {
		await insertBlock( 'Page Attachment' );
		await openPageAttachment();

		const defaultPageTitle = 'Sample Page';

		await searchAndChooseAttachment( defaultPageTitle );

		await page.waitForSelector( TITLE_SELECTOR );
		const titleInContent = await page.$eval( TITLE_SELECTOR, ( node ) => node.textContent );
		await expect( titleInContent ).toMatch( defaultPageTitle );
	} );

	it( 'should display chosen content in preview', async () => {
		await insertBlock( 'Page Attachment' );
		await openPageAttachment();

		const defaultPostTitle = 'Hello world!';
		await searchAndChooseAttachment( defaultPostTitle );

		await saveDraft();
		const editorPage = page;
		const previewPage = await openPreviewPage( editorPage, 'amp-story' );

		await previewPage.waitForSelector( TITLE_SELECTOR );
		const titleInContent = await previewPage.$eval( TITLE_SELECTOR, ( node ) => node.textContent );
		await expect( titleInContent ).toMatch( defaultPostTitle );
	} );

	it( 'should allow removing selected content', async () => {
		await insertBlock( 'Page Attachment' );
		await openPageAttachment();

		const defaultPostTitle = 'Hello world!';
		await searchAndChooseAttachment( defaultPostTitle );

		await clickButton( 'Remove Post' );
		// The input for choosing new post should appear again.
		await expect( page ).toMatchElement( '.block-editor-post-input input' );
	} );
} );
