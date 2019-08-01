/**
 * WordPress dependencies
 */
import { createNewPost, dragAndResize, getEditedPostContent } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { activateExperience, deactivateExperience, selectBlockByClassName } from '../../utils';

describe( 'Text Block', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	const textBlockClass = 'wp-block-amp-story-text';

	beforeEach( async () => {
		await createNewPost( { postType: 'amp_story' } );
		await page.waitForSelector( `.${ textBlockClass }.is-not-editing` );
		await selectBlockByClassName( textBlockClass );
	} );

	it( 'should not be in editable mode when selecting the block', async () => {
		await page.keyboard.type( 'Hello' );
		const content = await page.$eval( '.block-editor-block-list__layout .block-editor-block-list__block .wp-block-amp-amp-story-text', ( node ) => node.textContent );
		expect( content ).toStrictEqual( 'Write text…' );
	} );

	it( 'should go to editable mode after two clicks', async () => {
		const textToWrite = 'Hello';

		await page.click( `.${ textBlockClass }` );
		await page.keyboard.type( textToWrite );
		const content = await page.$eval( '.block-editor-block-list__layout .block-editor-block-list__block .wp-block-amp-amp-story-text', ( node ) => node.textContent );
		expect( content ).toStrictEqual( textToWrite );
	} );

	// @todo Dragging is not working for some reason.
	it.skip( 'should allow dragging the Text block from anywhere when not editing', async () => { // eslint-disable-line jest/no-disabled-tests
		const textBlock = await page.$( '.wp-block-amp-story-text-wrapper' );
		await dragAndResize( textBlock, { x: 0, y: 25 } );
		expect( await getEditedPostContent() ).toBe( '' );
	} );

	// @todo This test is useless until dragging actually works in the previous test.
	it.skip( 'should not allow dragging in editable mode', async () => { // eslint-disable-line jest/no-disabled-tests
		await page.click( `.${ textBlockClass }` );
		const textBlock = await page.$( '.wp-block-amp-story-text-wrapper' );
		await dragAndResize( textBlock, { x: 0, y: 25 } );
	} );
} );
