/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

/**
 * Internal dependencies
 */
import { DEFAULT_MOBILE_BREAKPOINT } from '../../../../assets/src/common/constants';

describe( 'Close button placement', () => {
	beforeEach( async () => {
		await visitAdminPage( 'admin.php', 'page=amp-onboarding-wizard' );
		await page.waitForSelector( '.amp-settings-nav__prev-next' );
	} );

	it( 'should show in footer above breakpoint, in sidebar below breakpoint', async () => {
		await expect( page ).toMatchElement( '.welcome' );

		const { height } = await page.viewport();
		await page.setViewport( { width: DEFAULT_MOBILE_BREAKPOINT, height } );
		await expect( page ).toMatchElement( '.amp-settings-nav .is-link', { text: /Close/ } );
		await expect( page ).not.toMatchElement( '.amp-stepper-container__header .is-link', { text: /Close/ } );

		await page.setViewport( { width: DEFAULT_MOBILE_BREAKPOINT - 1, height } );
		await expect( page ).not.toMatchElement( '.amp-settings-nav .is-link', { text: /Close/ } );
		await expect( page ).toMatchElement( '.amp-stepper-container__header .is-link', { text: /Close/ } );
	} );
} );
