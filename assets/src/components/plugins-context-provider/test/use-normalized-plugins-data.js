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
import { PluginsContextProvider } from '../index';
import { useNormalizedPluginsData } from '../use-normalized-plugins-data';

jest.mock( '../index' );

let returnValue = {};

function ComponentContainingHook() {
	returnValue = useNormalizedPluginsData();
	return null;
}

const Providers = ( { children, fetchingPlugins, plugins = [] } ) => (
	<ErrorContextProvider>
		<PluginsContextProvider plugins={ plugins } fetchingPlugins={ fetchingPlugins }>
			{ children }
		</PluginsContextProvider>
	</ErrorContextProvider>
);
Providers.propTypes = {
	children: PropTypes.any,
	fetchingPlugins: PropTypes.bool,
	plugins: PropTypes.array,
};

describe( 'useNormalizedPluginsData', () => {
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

	it( 'returns empty an array if plugins are being fetched', () => {
		act( () => {
			render(
				<Providers
					fetchingPlugins={ true }
					plugins={ [ 'foo' ] }
				>
					<ComponentContainingHook />
				</Providers>,
				container,
			);
		} );

		expect( returnValue ).toHaveLength( 0 );
	} );

	it( 'returns a normalized array of plugins', () => {
		act( () => {
			render(
				<Providers
					fetchingPlugins={ false }
					plugins={ [
						{
							author: {
								raw: 'Acme Inc.',
								rendered: '<a href="http://example.com">Acme Inc.</a>',
							},
							author_uri: {
								raw: 'http://example.com',
								rendered: 'http://example.com',
							},
							name: 'Acme Plugin',
							plugin: 'acme-inc',
							status: 'inactive',
							version: '1.0.1',
						},
						{
							author: 'AMP Project Contributors',
							author_uri: 'https://github.com/ampproject/amp-wp/graphs/contributors',
							name: {
								raw: 'AMP',
								rendered: '<strong>AMP</strong>',
							},
							plugin: 'amp/amp',
							status: 'active',
							version: '2.2.0-alpha',
						},
					] }
				>
					<ComponentContainingHook />
				</Providers>,
				container,
			);
		} );

		expect( returnValue ).toStrictEqual( {
			'acme-inc': {
				author: 'Acme Inc.',
				author_uri: 'http://example.com',
				name: 'Acme Plugin',
				plugin: 'acme-inc',
				status: 'inactive',
				slug: 'acme-inc',
				version: '1.0.1',
			},
			amp: {
				author: 'AMP Project Contributors',
				author_uri: 'https://github.com/ampproject/amp-wp/graphs/contributors',
				name: 'AMP',
				plugin: 'amp/amp',
				status: 'active',
				slug: 'amp',
				version: '2.2.0-alpha',
			},
		} );
	} );
} );
