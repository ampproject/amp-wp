/**
 * WordPress dependencies
 */
import { visitAdminPage } from '@wordpress/e2e-test-utils';

export async function assignMenuToLocation( menuLocationName ) {
	await visitAdminPage( 'nav-menus.php', '' );
	await page.waitForSelector( `input[name="menu-locations[${ menuLocationName }]"]` );
	await page.click( `input[name="menu-locations[${ menuLocationName }]"]` );
	await page.click( '#save_menu_footer' );
	await page.waitForSelector( '#message', { text: /has been updated/ } );
}
