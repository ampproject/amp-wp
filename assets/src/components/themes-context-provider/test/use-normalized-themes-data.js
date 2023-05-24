/**
 * External dependencies
 */
import { render } from '@testing-library/react';

/**
 * Internal dependencies
 */
import { ErrorContextProvider } from '../../error-context-provider';
import { ThemesContextProvider } from '../index';
import { useNormalizedThemesData } from '../use-normalized-themes-data';

jest.mock('../index');

describe('useNormalizedThemesData', () => {
	function setup({ fetchingThemes, themes }) {
		let returnValue;

		function ComponentContainingHook() {
			returnValue = useNormalizedThemesData();
			return null;
		}

		render(
			<ErrorContextProvider>
				<ThemesContextProvider
					fetchingThemes={fetchingThemes}
					themes={themes}
				>
					<ComponentContainingHook />
				</ThemesContextProvider>
			</ErrorContextProvider>
		);

		return returnValue;
	}

	it('returns empty an object if themes are being fetched', () => {
		const normalizedThemesData = setup({
			fetchingThemes: true,
			themes: [],
		});

		expect(normalizedThemesData).toStrictEqual({});
	});

	it('returns an object with normalized themes data', () => {
		const normalizedThemesData = setup({
			fetchingThemes: false,
			themes: [
				{
					author: {
						raw: 'the WordPress team',
						rendered:
							'<a href="https://wordpress.org/">the WordPress team</a>',
					},
					author_uri: {
						raw: 'https://wordpress.org/',
						rendered: 'https://wordpress.org/',
					},
					name: 'Twenty Fifteen',
					stylesheet: 'twentyfifteen',
					status: 'inactive',
					version: '3.0',
				},
				{
					author: 'the WordPress team',
					author_uri: 'https://wordpress.org/',
					name: {
						raw: 'Twenty Twenty',
						rendered: 'Twenty Twenty',
					},
					stylesheet: 'twentytwenty',
					status: 'active',
					version: '1.7',
				},
			],
		});

		expect(normalizedThemesData).toStrictEqual({
			twentyfifteen: {
				author: 'the WordPress team',
				author_uri: 'https://wordpress.org/',
				name: 'Twenty Fifteen',
				slug: 'twentyfifteen',
				stylesheet: 'twentyfifteen',
				status: 'inactive',
				version: '3.0',
			},
			twentytwenty: {
				author: 'the WordPress team',
				author_uri: 'https://wordpress.org/',
				name: 'Twenty Twenty',
				slug: 'twentytwenty',
				stylesheet: 'twentytwenty',
				status: 'active',
				version: '1.7',
			},
		});
	});

	it('identifies parent and child themes', () => {
		const normalizedThemesData = setup({
			fetchingThemes: false,
			themes: [
				{
					stylesheet: 'parentTheme',
					template: 'parentTheme',
					status: 'inactive',
				},
				{
					stylesheet: 'childTheme',
					template: 'parentTheme',
					status: 'active',
				},
			],
		});

		expect(normalizedThemesData).toStrictEqual({
			parentTheme: {
				slug: 'parentTheme',
				stylesheet: 'parentTheme',
				template: 'parentTheme',
				status: 'inactive',
				child: 'childTheme',
			},
			childTheme: {
				slug: 'childTheme',
				stylesheet: 'childTheme',
				template: 'parentTheme',
				status: 'active',
				parent: 'parentTheme',
			},
		});
	});
});
