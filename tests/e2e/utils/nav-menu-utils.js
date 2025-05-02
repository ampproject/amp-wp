/**
 * WordPress dependencies
 */
import { createMenu, deleteAllMenus } from '@wordpress/e2e-test-utils';

export async function createTestMenu(menuLocation = 'top') {
	await deleteAllMenus();
	await createMenu(
		{
			name: 'Test Menu 1',
			locations: [menuLocation],
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
		]
	);
}
