/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

describe( 'AMP settings screen reader themes carousel', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-options' );
		await page.addStyleTag( { content: 'html {scroll-behavior: auto !important;}' } );
	} );

	it( 'allows selection of carousel items', async () => {
		await expect( page ).toClick( '#template-mode-reader' );
		await expect( page ).toClick( '#reader-themes .components-panel__body-toggle' );

		await expect( page ).toMatchElement( '.amp-carousel__carousel' );
		await expect( page ).toMatchElement( '#theme-card__twentynineteen' );
		await expect( page ).toClick( '#theme-card__twentynineteen' );
		await expect( page ).toMatchElement( '#theme-card__twentynineteen:checked' );
	} );
} );

