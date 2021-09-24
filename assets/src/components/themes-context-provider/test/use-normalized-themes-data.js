/**
 * External dependencies
 */
import PropTypes from 'prop-types';
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

let returnValue = {};

function ComponentContainingHook( { skipInactive } ) {
	returnValue = useNormalizedThemesData( { skipInactive } );
	return null;
}
ComponentContainingHook.propTypes = {
	skipInactive: PropTypes.bool,
};

const Providers = ( { children, fetchingThemes, themes = [] } ) => (
	<ErrorContextProvider>
		<ThemesContextProvider themes={ themes } fetchingThemes={ fetchingThemes }>
			{ children }
		</ThemesContextProvider>
	</ErrorContextProvider>
);
Providers.propTypes = {
	children: PropTypes.any,
	fetchingThemes: PropTypes.bool,
	themes: PropTypes.array,
};

describe( 'useNormalizedThemesData', () => {
	let container = null;

	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		unmountComponentAtNode( container );
		container.remove();
		container = null;
		returnValue = {};
	} );

	it( 'returns empty an array if themes are being fetched', () => {
		act( () => {
			render(
				<Providers
					fetchingThemes={ true }
					themes={ [ 'foo' ] }
				>
					<ComponentContainingHook />
				</Providers>,
				container,
			);
		} );

		expect( returnValue ).toHaveLength( 0 );
	} );

	it( 'returns a normalized array of themes', () => {
		act( () => {
			render(
				<Providers
					fetchingThemes={ false }
					themes={ [
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
					] }
				>
					<ComponentContainingHook skipInactive={ false } />
				</Providers>,
				container,
			);
		} );

		expect( returnValue ).toMatchObject( {
			twentyfifteen: {
				author: 'the WordPress team',
				author_uri: 'https://wordpress.org/',
				name: 'Twenty Fifteen',
				stylesheet: 'twentyfifteen',
				status: 'inactive',
				version: '3.0',
			},
			twentytwenty: {
				author: 'the WordPress team',
				author_uri: 'https://wordpress.org/',
				name: 'Twenty Twenty',
				stylesheet: 'twentytwenty',
				status: 'active',
				version: '1.7',
			},
		} );
	} );

	it( 'skips inactive plugins', () => {
		act( () => {
			render(
				<Providers
					fetchingThemes={ false }
					themes={ [
						{
							stylesheet: 'twentyfifteen',
							status: 'inactive',
						},
						{
							stylesheet: 'twentytwenty',
							status: 'active',
						},
					] }
				>
					<ComponentContainingHook skipInactive={ true } />
				</Providers>,
				container,
			);
		} );

		expect( returnValue ).toMatchObject( {
			twentytwenty: {
				stylesheet: 'twentytwenty',
				status: 'active',
			},
		} );
	} );
} );
