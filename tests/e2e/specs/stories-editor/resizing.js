/**
 * WordPress dependencies
 */
import { createNewPost, dragAndResize } from '@wordpress/e2e-test-utils';

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
		const el = document.querySelector( '.wp-block.is-selected' );
		return {
			positionLeft: el.style.left,
			positionTop: el.style.top,
		};
	} );
}

/**
 * Returns the selected block's width and height.
 *
 * @returns {Promise<{width: number, height: number}>} Block dimensions.
 */
// eslint-disable-next-line require-await
async function getSelectedBlockDimensions() {
	return page.evaluate( () => {
		const el = document.querySelector( '.wp-block.is-selected' );
		return {
			width: el.clientWidth,
			height: el.clientHeight,
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

	describe( 'Text block', () => {
		beforeEach( async () => {
			await createNewPost( { postType: 'amp_story' } );
			// Select the Text block inserted by default.
			await selectBlockByClassName( 'wp-block-amp-story-text' );
		} );

		const defaultWidth = 250;
		const textBlockMinWidth = 40;

		it( 'it should not resize smaller than the set minimum width and height', async () => {
			const textBlockMinHeight = 30;
			let height, width;
			const resizableHandleBottom = await page.$( '.wp-block.is-selected .components-resizable-box__handle-bottom' );
			await dragAndResize( resizableHandleBottom, { x: 0, y: -250 } );
			height = await page.evaluate( () => document.querySelector( '.wp-block.is-selected' ).clientHeight );
			expect( height ).toStrictEqual( textBlockMinHeight );

			const resizableHandleTop = await page.$( '.wp-block.is-selected .components-resizable-box__handle-top' );
			await dragAndResize( resizableHandleTop, { x: 0, y: 250 } );
			height = await page.evaluate( () => document.querySelector( '.wp-block.is-selected' ).clientHeight );
			expect( height ).toStrictEqual( textBlockMinHeight );

			const resizableHandleLeft = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left' );
			await dragAndResize( resizableHandleLeft, { x: 300, y: 0 } );
			width = await page.evaluate( () => document.querySelector( '.wp-block.is-selected' ).clientWidth );
			expect( width ).toStrictEqual( textBlockMinWidth );

			const resizableHandleRight = await page.$( '.wp-block.is-selected .components-resizable-box__handle-right' );
			await dragAndResize( resizableHandleRight, { x: -300, y: 0 } );
			width = await page.evaluate( () => document.querySelector( '.wp-block.is-selected' ).clientWidth );
			expect( width ).toStrictEqual( textBlockMinWidth );
		} );

		it( 'should not change the block position when resizing from left handle and minimum width has been reached', async () => {
			const resizableHandleLeft = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left' );
			await dragAndResize( resizableHandleLeft, { x: defaultWidth - textBlockMinWidth, y: 0 } );
			const width = await page.evaluate( () => document.querySelector( '.wp-block.is-selected' ).clientWidth );
			expect( width ).toStrictEqual( textBlockMinWidth );

			const positionLeft = await page.evaluate( () => document.querySelector( '.wp-block.is-selected' ).style.left );
			expect( positionLeft ).toContain( '%' );

			// Drag the resizer more.
			await dragAndResize( resizableHandleLeft, { x: 300, y: 0 } );
			const positionLeftAfter = await page.evaluate( () => document.querySelector( '.wp-block.is-selected' ).style.left );
			// Verify that that the positionLeft has not changed.
			expect( positionLeftAfter ).toStrictEqual( positionLeft );
		} );

		it( 'should change the width and height correctly when resizing from topLeft corner', async () => {
			const resizableHandleTopLeft = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left.components-resizable-box__handle-top' );
			await dragAndResize( resizableHandleTopLeft, { x: -100, y: -100 } );
			const { width, height } = await getSelectedBlockDimensions();
			expect( width ).toStrictEqual( 350 );
			expect( height ).toStrictEqual( 160 );
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
			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left' );
			await dragAndResize( handle, { x: 100, y: 0 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '9.84%' );
			expect( positionTop ).toStrictEqual( '7.69%' );
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: topLeft', async () => {
			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left.components-resizable-box__handle-top' );
			await dragAndResize( handle, { x: 100, y: -100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '32.8%' );
			expect( positionTop ).toStrictEqual( '-2.76%' );
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: top', async () => {
			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-top' );
			await dragAndResize( handle, { x: 0, y: -100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '1.07%' );
			expect( positionTop ).toStrictEqual( '7%' );
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: topRight', async () => {
			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-right.components-resizable-box__handle-top' );
			await dragAndResize( handle, { x: -100, y: 100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '5%' );
			expect( positionTop ).toStrictEqual( '10%' );
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: right', async () => {
			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-right' );
			await dragAndResize( handle, { x: -100, y: 0 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '5%' );
			expect( positionTop ).toStrictEqual( '10%' );
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: bottomRight', async () => {
			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-right.components-resizable-box__handle-bottom' );
			await dragAndResize( handle, { x: 100, y: 100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '5%' );
			expect( positionTop ).toStrictEqual( '10%' );
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: bottom', async () => {
			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-bottom' );
			await dragAndResize( handle, { x: 0, y: -100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '1.07%' );
			expect( positionTop ).toStrictEqual( '11.68%' );
		} );

		it( 'should change the top and left position correctly when resizing a rotated block: bottomLeft', async () => {
			const handle = await page.$( '.wp-block.is-selected .components-resizable-box__handle-left.components-resizable-box__handle-bottom' );
			await dragAndResize( handle, { x: -100, y: -100 } );

			const { positionLeft, positionTop } = await getSelectedBlockPosition();
			expect( positionLeft ).toStrictEqual( '14.03%' );
			expect( positionTop ).toStrictEqual( '5.78%' );
		} );
	} );

	describe( 'Author block', () => {
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
			expect( positionTop ).toStrictEqual( '8.04%' );
		} );
	} );
} );
