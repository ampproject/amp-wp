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
} from '../../utils';

describe( 'Stories Editor Screen', () => {
	beforeAll( async () => {
		await activateExperience( 'stories' );
	} );

	afterAll( async () => {
		await deactivateExperience( 'stories' );
	} );

	it( 'should not display CTA icon when only one Page is present', async () => {
		await visitAdminPage( 'post-new.php', 'post_type=amp_story' );

		const nodes = await page.$x(
			'//div[@id="amp-story-shortcuts"]//button[@aria-label="Call to Action"]'
		);
		expect( nodes ).toHaveLength( 0 );
	} );

	it( 'should display CTA icon when second Page is added', async () => {
		await createNewPost( { postType: 'amp_story' } );

		await insertBlock( 'Page' );
		const nodes = await page.$x(
			'//div[@id="amp-story-shortcuts"]//button[@aria-label="Call to Action"]'
		);
		expect( nodes ).toHaveLength( 1 );
	} );

	it( 'should display validation error when CTA block is on the first Page', async () => {
		await createNewPost( { postType: 'amp_story' } );

		const firstPageClientId = ( await getAllBlocks() )[ 0 ].clientId;

		await insertBlock( 'Page' );

		await insertBlock( 'Call to Action' );
		await goToPreviousPage();

		await page.waitForSelector( `#block-${ firstPageClientId }.amp-page-active` );
		await selectBlockByClientId( firstPageClientId );

		await clickButtonByLabel( 'More options' );
		await clickButton( 'Remove Block' );

		const errorSelector = '.wp-block .block-editor-warning__message';
		await page.waitForSelector( errorSelector );
		const element = await page.$( errorSelector );
		const text = await ( await element.getProperty( 'textContent' ) ).jsonValue();
		expect( text ).toStrictEqual( 'Call to Action: This block can not be used on the first page.' );
	} );
} );
