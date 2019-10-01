/**
 * WordPress dependencies
 */
import {
	createNewPost,
	getAllBlocks,
	saveDraft,
	selectBlockByClientId,
} from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	activateExperience,
	deactivateExperience,
	uploadMedia,
	openPreviewPage,
	clickButton,
	openMediaInserter,
} from '../../utils';

const LARGE_IMAGE = 'large-image-36521.jpg';
const LARGE_VIDEO = 'clothes-hanged-to-dry-1295231.mp4';
const MEDIA_LIBRARY_BUTTON = '.editor-media-placeholder__media-library-button';
const SELECT_BUTTON = '.media-modal button.media-button-select';

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

	it( 'should display media inserter icon', async () => {
		const nodes = await page.$x(
			'//div[@id="amp-story-media-inserter"]//button[@aria-label="Insert Media"]'
		);
		expect( nodes ).toHaveLength( 1 );
	} );

	it( 'should be possible to add Background Image', async () => {
		// Click the media selection button.
		await openMediaInserter();
		await clickButton( 'Insert Background Image' );
		await uploadMedia( LARGE_IMAGE );

		// Insert the image.
		await page.click( '.media-modal button.media-button-select' );

		// Wait for media to be inserted.
		await page.waitForSelector( '.components-focal-point-picker-wrapper' );
		await saveDraft();
		await page.reload();

		const editorPage = page;
		const previewPage = await openPreviewPage( editorPage, 'amp-story' );
		await previewPage.waitForSelector( '.amp-story-block-wrapper' );

		const [ elementHandle ] = await previewPage.$x( '//amp-story-page/amp-story-grid-layer/amp-img/@src' );
		const srcHandle = await elementHandle.getProperty( 'value' );
		const src = await srcHandle.jsonValue();
		expect( src ).toContain( '.jpg' );
		expect( src ).toContain( 'wp-content/uploads' );
	} );

	it( 'should be possible to update Background Image', async () => {
		// Click the media selection button.
		await openMediaInserter();
		await clickButton( 'Insert Background Image' );
		await uploadMedia( LARGE_IMAGE );

		// Insert the image.
		await page.click( '.media-modal button.media-button-select' );

		// Wait for media to be inserted.
		await page.waitForSelector( '.components-focal-point-picker-wrapper' );

		await openMediaInserter();

		const nodes = await page.$x(
			`//button[contains(text(), 'Update Background Image')]`
		);
		expect( nodes ).toHaveLength( 1 );
	} );

	it( 'should be possible to add Background Video', async () => {
		// Click the media selection button.
		await openMediaInserter();
		await clickButton( 'Insert Background Video' );
		await uploadMedia( LARGE_VIDEO );

		// Insert the uploaded video.
		await page.click( '.media-modal button.media-button-select' );

		// Wait for video to be inserted.
		await page.waitForSelector( '.editor-amp-story-page-background' );

		// Wait for poster to be extracted.
		await expect( page ).toMatchElement( '#editor-amp-story-page-poster' );
	} );

	it( 'should be possible to add Image block', async () => {
		// Click the media selection button.
		await openMediaInserter();
		await clickButton( 'Insert Image' );
		// Click the media library button.
		await page.waitForSelector( MEDIA_LIBRARY_BUTTON );

		await page.click( MEDIA_LIBRARY_BUTTON );
		await uploadMedia( LARGE_IMAGE );

		// Select the image from the Media Library.
		await page.waitForSelector( SELECT_BUTTON );
		await page.click( SELECT_BUTTON );

		// Wait for image to appear in the block.
		await page.waitForSelector( '.wp-block-image img' );
		await expect( page ).toMatchElement( '.wp-block-image img' );
	} );

	it( 'should be possible to add Video block', async () => {
		// Click the media selection button.
		await openMediaInserter();
		await clickButton( 'Insert Video' );
		// Click the media library button.
		await page.waitForSelector( MEDIA_LIBRARY_BUTTON );

		await page.click( MEDIA_LIBRARY_BUTTON );
		await uploadMedia( LARGE_VIDEO );

		// Select the image from the Media Library.
		await page.waitForSelector( SELECT_BUTTON );
		await page.click( SELECT_BUTTON );

		// Wait for image to appear in the block.
		await page.waitForSelector( '.wp-block-video video' );
		await expect( page ).toMatchElement( '.wp-block-video video' );
	} );

	it( 'should dropdown title should change after sidebar upload', async () => {
		await selectBlockByClientId(
			( await getAllBlocks() )[ 0 ].clientId
		);
		// Click the media selection button.
		await page.waitForSelector( '.editor-amp-story-page-background' );
		await page.click( '.editor-amp-story-page-background' );
		await uploadMedia( LARGE_IMAGE );

		// Insert the image.
		await page.click( '.media-modal button.media-button-select' );

		// Wait for media to be inserted.
		await page.waitForSelector( '.components-focal-point-picker-wrapper' );
		// Click the media selection button.
		await openMediaInserter();
		const nodes = await page.$x(
			`//button[contains(text(), 'Update Background Image')]`
		);
		expect( nodes ).toHaveLength( 1 );
	} );
} );
