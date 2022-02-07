/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * WordPress dependencies
 */
import { render, unmountComponentAtNode } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { ErrorContextProvider } from '../../error-context-provider';
import { ThemesContextProvider } from '../index';
import { useNormalizedThemesData } from '../use-normalized-themes-data';

jest.mock( '../index' );

describe( 'useNormalizedThemesData', () => {
	let container = null;

	function setup( { fetchingThemes, themes } ) {
		let returnValue;

		function ComponentContainingHook() {
			returnValue = useNormalizedThemesData();
			return null;
		}

		act( () => {
			render(
				<ErrorContextProvider>
					<ThemesContextProvider fetchingThemes={ fetchingThemes } themes={ themes }>
						<ComponentContainingHook />
					</ThemesContextProvider>
				</ErrorContextProvider>,
				container,
			);
		} );

		return returnValue;
	}

	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		unmountComponentAtNode( container );
		container.remove();
		container = null;
	} );

	it( 'returns empty an object if themes are being fetched', () => {
		const normalizedThemesData = setup( {
			fetchingThemes: true,
			themes: [],
		} );

		expect( normalizedThemesData ).toStrictEqual( {} );
	} );

	it( 'returns an object with normalized themes data', () => {
		const normalizedThemesData = setup( {
			fetchingThemes: false,
			themes: [
				{
					author: {
						raw: 'the WordPress team',
						rendered: '<a href="https://wordpress.org/">the WordPress team</a>',
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
		} );

		expect( normalizedThemesData ).toStrictEqual( {
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
		} );
	} );
} );
