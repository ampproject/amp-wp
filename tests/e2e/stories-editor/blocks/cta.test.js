/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { insertStoryBlockBySearch } from '../utils';

describe( 'Stories Editor Screen', () => {
	it( 'Should not display CTA icon when only one Page is present', async () => {

		await visitAdminPage( 'post-new.php', 'post_type=amp_story' );

		const nodes = await page.$x(
			'//div[@id="amp-story-shortcuts"]//button[@aria-label="Call to Action"]'
		);
		expect( nodes.length ).toEqual( 0 );
	} );

	it( 'Should display CTA icon when second Page is added', async () => {

		await visitAdminPage( 'post-new.php', 'post_type=amp_story' );

		await insertStoryBlockBySearch( 'Page' );

		const nodes = await page.$x(
			'//div[@id="amp-story-shortcuts"]//button[@aria-label="Call to Action"]'
		);
		expect( nodes.length ).toEqual( 1 );
	} );

	it( 'Should not allow negative top position for CTA block when dragging', async () => {
		await visitAdminPage( 'post-new.php', 'post_type=amp_story' );

		await insertStoryBlockBySearch( 'Page' );
		await insertStoryBlockBySearch( 'Call to Action' );
		// Test dragging.
		// @todo Dragging is not working.
		await page.click( '.amp-block-story-cta__link' );
		await page.keyboard.type( 'Hello World' );
		const element = await page.$( '.amp-story-cta-button' );

		const {
			x: elementX,
			y: elementY,
			width: elementWidth,
			height: elementHeight,
		} = await element.boundingBox();

		const originX = elementX + 3;
		const originY = elementY + ( elementHeight / 2 );

		await page.mouse.move( originX, originY );
		await page.mouse.down();
		await page.mouse.move( originX + 15, originY + 15 );
		await page.mouse.up();

		const html = await page.evaluate( ctaButton => ctaButton.outerHTML, element );
		expect( html ).toBe('');
	} )
} );
