/**
 * WordPress dependencies
 */
import { createMenu, deleteAllMenus, visitAdminPage } from '@wordpress/e2e-test-utils';

export async function createTestMenu() {
	await deleteAllMenus();
	await createMenu(
		{
			name: 'Test Menu 1',
		},
		[
			{
				title: 'WordPress.org',
				url: 'https://wordpress.org',
				menu_order: 1,
			},
			{
				title: 'Wikipedia.org',
				url: 'https://wikipedia.org',
				menu_order: 2,
			},
			{
				title: 'Google',
				url: 'https://google.com',
				menu_order: 3,
				parent: 1,
			},
		],
	);
}

export async function assignMenuToLocation( menuLocationName ) {
	await visitAdminPage( 'nav-menus.php', '' );

	// Bail out if there is no menu location or it is already selected.
	const menuLocationCheckbox = await page.$( `input:not(:checked)[name="menu-locations[${ menuLocationName }]"]` );
	if ( ! menuLocationCheckbox ) {
		return;
	}

	await menuLocationCheckbox.click();
	await page.click( '#save_menu_footer' );
	await page.waitForSelector( '#message', { text: /has been updated/ } );
}
