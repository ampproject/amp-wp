/**
 * WordPress dependencies
 */
import { createNewPost, dragAndResize, selectBlockByClientId, getAllBlocks } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import {
	activateExperience,
	deactivateExperience,
	selectBlockByClassName,
	insertBlock,
} from '../../utils';

/**
 * Rotates the selected block from its default position.
 */
async function rotateSelectedBlock() {
	const rotationHandle = await page.$( '.wp-block.is-selected .rotatable-box-wrap__handle' );
	await dragAndResize( rotationHandle, { x: 250, y: 0 } );
}

/**
 * Get selected block's top and left position values.
 *
 * @return {Promise<{positionLeft:number, positionTop:number}>} Block position.
 */
// eslint-disable-next-line require-await
async function getSelectedBlockPosition() {
	return page.evaluate( () => {
		const el = document.querySelector( '.wp-block.is-selected' ).parentNode;
		return {
			positionLeft: el.style.left,
			positionTop: el.style.top,
		};
	} );
}

/**
 * Returns the selected block's width and height.
 *
 * This will look at the resizable box inside the block for the true visual
 * size as represented by the drawn border and position of resize handles.
 *
 * @return {Promise<{width: number, height: number}>} Block dimensions.
 */
// eslint-disable-next-line require-await
async function getSelectedBlockDimensions() {
	return page.evaluate( () => {
		const el = document.querySelector( '.wp-block.is-selected .components-resizable-box__container' );
		return {
			width: el.clientWidth,
			height: el.clientHeight,
		};
	} );
}

/**
 * Returns the selected block's text box's width and height.
 *
 * @return {Promise<{width: number, height: number}>} Text box dimensions.
 */
// eslint-disable-next-line require-await
async function getSelectedTextBoxDimensions() {
	return page.evaluate( () => {
		const textbox = document.querySelector( '.wp-block.is-selected .wp-block-amp-amp-story-text' );
		return {
			height: textbox.clientHeight,
			offset: textbox.offsetTop,
		};
	} );
}

describe( 'Resizing', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	const textBlockMinWidth = 40;
	const textBlockMinHeight = 30;

	describe( 'Text block', () => {
		beforeEach( async () => {
			await createNewPost( { postType: 'amp_story' } );
			// Select the Text block inserted by default.
			await selectBlockByClassName( 'wp-block-amp-story-text' );
		} );

		const defaultWidth = 250;

		it( 'should not resize smaller than the set minimum width and height', async () => {
			let dimensions;
			const resizableHandleBottom = await page.$( '.wp-block.is-selected .components-resizable-box__handle-bottom' );
			await dragAndResize( resizableHandleBottom, { x: 0, y: -250 } );
			dimensions = await getSelectedBlockDimensions();
			expect( dimensions.height ).toStrictEqual( textBlockMinHeight );

			const resizableHandleTop = await page.$( '.wp-block.is-selected .components-resizable-box__handle-top' );
			await dragAndResize( resizableHandleTop, { x: 0, y: 250 } );
			dimensions = await getSelectedBlockDimensions();
			expect( dimensions.height ).toStrictEqual( textBlockMinHeight );

			const resizableHandleLeft = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left' );
			await dragAndResize( resizableHandleLeft, { x: 300, y: 0 } );
			dimensions = await getSelectedBlockDimensions();
			expect( dimensions.width ).toStrictEqual( textBlockMinWidth );

			const resizableHandleRight = await page.$( '.wp-block.is-selected .components-resizable-box__handle-right' );
			await dragAndResize( resizableHandleRight, { x: -300, y: 0 } );
			dimensions = await getSelectedBlockDimensions();
			expect( dimensions.width ).toStrictEqual( textBlockMinWidth );
		} );

		it( 'should not change the block position when resizing from left handle and minimum width has been reached', async () => {
			const resizableHandleLeft = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left' );
			await dragAndResize( resizableHandleLeft, { x: defaultWidth - textBlockMinWidth, y: 0 } );
			const { width } = await getSelectedBlockDimensions();
			expect( width ).toStrictEqual( textBlockMinWidth );

			const positionLeft = await page.evaluate( () => document.querySelector( '.wp-block.is-selected' ).parentNode.style.left );
			expect( positionLeft ).toContain( '%' );

			// Drag the resizer more.
			await dragAndResize( resizableHandleLeft, { x: 300, y: 0 } );
			const positionLeftAfter = await page.evaluate( () => document.querySelector( '.wp-block.is-selected' ).parentNode.style.left );
			// Verify that that the positionLeft has not changed.
			expect( positionLeftAfter ).toStrictEqual( positionLeft );
		} );

		it( 'should change the width and height correctly when resizing: topLeft', async () => {
			const resizableHandleTopLeft = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left.components-resizable-box__handle-top' );
			await dragAndResize( resizableHandleTopLeft, { x: -100, y: -100 } );
			const { width, height } = await getSelectedBlockDimensions();
			expect( width ).toStrictEqual( 350 );
			expect( height ).toStrictEqual( 160 );
		} );

		it( 'should change the top position correctly when resizing: topRight', async () => {
			const { positionLeft: positionLeftBefore, positionTop: positionTopBefore } = await getSelectedBlockPosition();
			expect( positionLeftBefore ).toStrictEqual( '5%' );
			expect( positionTopBefore ).toStrictEqual( '10%' );

			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-right.components-resizable-box__handle-top' );
			await dragAndResize( handle, { x: -100, y: 100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '4.88%' );
			expect( positionTop ).toStrictEqual( '15.37%' );
		} );

		it( 'should not change the top and left position when resizing: right', async () => {
			const { positionLeft: positionLeftBefore, positionTop: positionTopBefore } = await getSelectedBlockPosition();
			expect( positionLeftBefore ).toStrictEqual( '5%' );
			expect( positionTopBefore ).toStrictEqual( '10%' );

			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-right' );
			await dragAndResize( handle, { x: -100, y: 0 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '4.88%' );
			expect( positionTop ).toStrictEqual( '9.95%' );
		} );

		it( 'should not change the top and left position when resizing: bottomRight', async () => {
			const { positionLeft: positionLeftBefore, positionTop: positionTopBefore } = await getSelectedBlockPosition();
			expect( positionLeftBefore ).toStrictEqual( '5%' );
			expect( positionTopBefore ).toStrictEqual( '10%' );

			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-right.components-resizable-box__handle-bottom' );
			await dragAndResize( handle, { x: 100, y: 100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '4.88%' );
			expect( positionTop ).toStrictEqual( '9.95%' );
		} );

		it( 'should not change the top and left position when resizing: bottom', async () => {
			const { positionLeft: positionLeftBefore, positionTop: positionTopBefore } = await getSelectedBlockPosition();
			expect( positionLeftBefore ).toStrictEqual( '5%' );
			expect( positionTopBefore ).toStrictEqual( '10%' );

			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-bottom' );
			await dragAndResize( handle, { x: 0, y: -100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '4.88%' );
			expect( positionTop ).toStrictEqual( '9.95%' );
		} );

		it( 'should change the left position correctly when resizing: bottomLeft', async () => {
			const { positionLeft: positionLeftBefore, positionTop: positionTopBefore } = await getSelectedBlockPosition();
			expect( positionLeftBefore ).toStrictEqual( '5%' );
			expect( positionTopBefore ).toStrictEqual( '10%' );

			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left.components-resizable-box__handle-bottom' );
			await dragAndResize( handle, { x: -100, y: -100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '-25.61%' );
			expect( positionTop ).toStrictEqual( '9.95%' );
		} );

		it( 'should keep text content height when resizing when max font size', async () => {
			// click to enable edit and input text to have non-empty textbox
			await page.click( '.wp-block-amp-story-text' );
			await page.keyboard.type( 'Hello' );

			// deselect element again by clicking the background and then reselect element (but now not in editable mode)
			await selectBlockByClientId( ( await getAllBlocks() )[ 0 ].clientId );
			await selectBlockByClassName( 'wp-block-amp-story-text' );

			// resize to make sure font-size will be maximum
			const resizableHandleBottom = await page.$( '.wp-block.is-selected .components-resizable-box__handle-bottom' );
			await dragAndResize( resizableHandleBottom, { x: 0, y: 100 } );

			const initialDimensions = await getSelectedTextBoxDimensions();

			await dragAndResize( resizableHandleBottom, { x: 0, y: 150 } );
			await getSelectedBlockDimensions();

			const newDimensions = await getSelectedTextBoxDimensions();

			expect( newDimensions.height ).toStrictEqual( initialDimensions.height );
		} );

		it( 'should keep text content vertically centered when resizing', async () => {
			// click to enable edit and input text to have non-empty textbox
			await page.click( '.wp-block-amp-story-text' );
			await page.keyboard.type( 'Hello' );

			// deselect element again by clicking the background and then reselect element (but now not in editable mode)
			await selectBlockByClientId( ( await getAllBlocks() )[ 0 ].clientId );
			await selectBlockByClassName( 'wp-block-amp-story-text' );

			// create helper function to check text box dimensions compared to block dimensions
			const checkDimensions = async () => {
				const textBoxDimensions = await getSelectedTextBoxDimensions();
				const blockDimensions = await getSelectedBlockDimensions();

				const blockHeight = blockDimensions.height;
				const textBoxPlusDoubleOffset = textBoxDimensions.height + ( textBoxDimensions.offset * 2 );

				// the two heights won't match perfectly, but should be within a few pixels
				// 2, 3 and 4 pixel difference has been spotted in the wild!
				const difference = Math.abs( blockHeight - textBoxPlusDoubleOffset );

				expect( difference ).toBeLessThan( 5 );
			};

			await checkDimensions();

			const resizableHandleBottom = await page.$( '.wp-block.is-selected .components-resizable-box__handle-bottom' );
			await dragAndResize( resizableHandleBottom, { x: 0, y: 150 } );
			await getSelectedBlockDimensions();

			await checkDimensions();
		} );
	} );

	describe( 'Non-Fitted Text block', () => {
		beforeEach( async () => {
			await createNewPost( { postType: 'amp_story' } );
			// Select the Text block inserted by default.
			await selectBlockByClassName( 'wp-block-amp-story-text' );
			// Click the toggle to disable automatic fitting
			const fitToggle = await page.waitForSelector( '.components-toggle-control input' );
			await fitToggle.click();
		} );

		it( 'should not resize smaller than the set minimum width: bottom', async () => {
			const resizableHandleBottom = await page.$( '.wp-block.is-selected .components-resizable-box__handle-bottom' );
			await dragAndResize( resizableHandleBottom, { x: 0, y: -250 } );
			const dimensions = await getSelectedBlockDimensions();
			expect( dimensions.height ).toStrictEqual( textBlockMinHeight );
		} );

		it( 'should not resize smaller than the set minimum height: top', async () => {
			const resizableHandleTop = await page.$( '.wp-block.is-selected .components-resizable-box__handle-top' );
			await dragAndResize( resizableHandleTop, { x: 0, y: 250 } );
			const dimensions = await getSelectedBlockDimensions();
			expect( dimensions.height ).toStrictEqual( textBlockMinHeight );
		} );

		it( 'should not resize smaller than the set minimum width: left', async () => {
			const resizableHandleLeft = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left' );
			await dragAndResize( resizableHandleLeft, { x: 300, y: 0 } );
			const dimensions = await getSelectedBlockDimensions();
			expect( dimensions.width ).toStrictEqual( textBlockMinWidth );
		} );

		it( 'should not resize smaller than the set minimum width: right', async () => {
			const resizableHandleRight = await page.$( '.wp-block.is-selected .components-resizable-box__handle-right' );
			await dragAndResize( resizableHandleRight, { x: -300, y: 0 } );
			const dimensions = await getSelectedBlockDimensions();
			expect( dimensions.width ).toStrictEqual( textBlockMinWidth );
		} );

		it( 'should not move the position of the block when the width is not changing: left', async () => {
			// Ensure first that the block is already minimum width.
			const resizableHandleLeft = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left' );
			await dragAndResize( resizableHandleLeft, { x: 300, y: 0 } );
			const dimensions = await getSelectedBlockDimensions();
			expect( dimensions.width ).toStrictEqual( textBlockMinWidth );

			// Get the initial position.
			const { positionLeft: positionLeftBefore } = await getSelectedBlockPosition();
			// Try resizing again.
			await dragAndResize( resizableHandleLeft, { x: 300, y: 0 } );
			const { positionLeft } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( positionLeftBefore );

			// Rotate the block and try again. This will rotate the block -75 degrees.
			await rotateSelectedBlock();
			await dragAndResize( resizableHandleLeft, { x: 300, y: 0 } );
			const { positionLeft: positionLeftRotated } = await getSelectedBlockPosition();
			expect( positionLeftRotated ).toStrictEqual( positionLeftBefore );
		} );

		it( 'should not move the position of the block when the height is not changing: top', async () => {
			// Ensure first that the block is already minimum height.
			const resizableHandleTop = await page.$( '.wp-block.is-selected .components-resizable-box__handle-top' );
			await dragAndResize( resizableHandleTop, { x: 0, y: 250 } );
			const dimensions = await getSelectedBlockDimensions();
			expect( dimensions.height ).toStrictEqual( textBlockMinHeight );

			// Get the initial position.
			const { positionTop: positionTopBefore } = await getSelectedBlockPosition();
			// Try resizing again.
			await dragAndResize( resizableHandleTop, { x: 0, y: 250 } );
			const { positionTop } = await getSelectedBlockPosition();
			expect( positionTop ).toStrictEqual( positionTopBefore );

			// Rotate the block and try again. This will rotate the block -75 degrees.
			await rotateSelectedBlock();
			await dragAndResize( resizableHandleTop, { x: 0, y: 250 } );
			const { positionTop: positionTopRotated } = await getSelectedBlockPosition();
			expect( positionTopRotated ).toStrictEqual( positionTopBefore );
		} );
	} );

	describe( 'Rotated Text block', () => {
		beforeEach( async () => {
			await createNewPost( { postType: 'amp_story' } );
			// Select the Text block inserted by default.
			await selectBlockByClassName( 'wp-block-amp-story-text' );
			await rotateSelectedBlock();
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: left', async () => {
			const { positionLeft: positionLeftBefore, positionTop: positionTopBefore } = await getSelectedBlockPosition();
			expect( positionLeftBefore ).toStrictEqual( '5%' );
			expect( positionTopBefore ).toStrictEqual( '10%' );

			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left' );
			await dragAndResize( handle, { x: 100, y: 0 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '9.84%' );
			expect( positionTop ).toStrictEqual( '7.69%' );
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: topLeft', async () => {
			const { positionLeft: positionLeftBefore, positionTop: positionTopBefore } = await getSelectedBlockPosition();
			expect( positionLeftBefore ).toStrictEqual( '5%' );
			expect( positionTopBefore ).toStrictEqual( '10%' );

			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left.components-resizable-box__handle-top' );
			await dragAndResize( handle, { x: 100, y: -100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '32.8%' );
			expect( positionTop ).toStrictEqual( '2.66%' );
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: top', async () => {
			const { positionLeft: positionLeftBefore, positionTop: positionTopBefore } = await getSelectedBlockPosition();
			expect( positionLeftBefore ).toStrictEqual( '5%' );
			expect( positionTopBefore ).toStrictEqual( '10%' );

			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-top' );
			await dragAndResize( handle, { x: 0, y: -100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '1.07%' );
			expect( positionTop ).toStrictEqual( '7%' );
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: topRight', async () => {
			const { positionLeft: positionLeftBefore, positionTop: positionTopBefore } = await getSelectedBlockPosition();
			expect( positionLeftBefore ).toStrictEqual( '5%' );
			expect( positionTopBefore ).toStrictEqual( '10%' );

			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-right.components-resizable-box__handle-top' );
			await dragAndResize( handle, { x: -100, y: 100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '8.3%' );
			expect( positionTop ).toStrictEqual( '12.59%' );
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: right', async () => {
			const { positionLeft: positionLeftBefore, positionTop: positionTopBefore } = await getSelectedBlockPosition();
			expect( positionLeftBefore ).toStrictEqual( '5%' );
			expect( positionTopBefore ).toStrictEqual( '10%' );

			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-right' );
			await dragAndResize( handle, { x: -100, y: 0 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '7.8%' );
			expect( positionTop ).toStrictEqual( '12.21%' );
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: bottomRight', async () => {
			const { positionLeft: positionLeftBefore, positionTop: positionTopBefore } = await getSelectedBlockPosition();
			expect( positionLeftBefore ).toStrictEqual( '5%' );
			expect( positionTopBefore ).toStrictEqual( '10%' );

			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-right.components-resizable-box__handle-bottom' );
			await dragAndResize( handle, { x: 100, y: 100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '30.9%' );
			expect( positionTop ).toStrictEqual( '7.91%' );
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: bottom', async () => {
			const { positionLeft: positionLeftBefore, positionTop: positionTopBefore } = await getSelectedBlockPosition();
			expect( positionLeftBefore ).toStrictEqual( '5%' );
			expect( positionTopBefore ).toStrictEqual( '10%' );

			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-bottom' );
			await dragAndResize( handle, { x: 0, y: -100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '1.07%' );
			expect( positionTop ).toStrictEqual( '11.68%' );
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: bottomLeft', async () => {
			const { positionLeft: positionLeftBefore, positionTop: positionTopBefore } = await getSelectedBlockPosition();
			expect( positionLeftBefore ).toStrictEqual( '5%' );
			expect( positionTopBefore ).toStrictEqual( '10%' );

			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left.components-resizable-box__handle-bottom' );
			await dragAndResize( handle, { x: -100, y: -100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '14.03%' );
			expect( positionTop ).toStrictEqual( '5.78%' );
		} );
	} );

	describe( 'Rotated Author block', () => {
		beforeEach( async () => {
			await createNewPost( { postType: 'amp_story' } );
			await insertBlock( 'Author' );
			// Select the Text block inserted by default.
			await selectBlockByClassName( 'wp-block-amp-amp-story-post-author' );
			await rotateSelectedBlock();
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: bottomLeft', async () => {
			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left.components-resizable-box__handle-bottom' );
			await dragAndResize( handle, { x: -100, y: -100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '15.5%' );
			expect( positionTop ).toStrictEqual( '15.24%' );
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: topLeft', async () => {
			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left.components-resizable-box__handle-top' );
			await dragAndResize( handle, { x: 100, y: -100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '31.32%' );
			expect( positionTop ).toStrictEqual( '11.65%' );
		} );
	} );
} );
