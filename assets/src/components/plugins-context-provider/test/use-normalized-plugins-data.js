/**
 * External dependencies
 */
import { render } from '@testing-library/react';
import { describe, expect, it, jest } from '@jest/globals';

/**
 * Internal dependencies
 */
import { ErrorContextProvider } from '../../error-context-provider';
import { PluginsContextProvider } from '../index';
import { useNormalizedPluginsData } from '../use-normalized-plugins-data';

jest.mock('../index');

describe('useNormalizedPluginsData', () => {
	function setup({ fetchingPlugins, plugins }) {
		let returnValue;

		function ComponentContainingHook() {
			returnValue = useNormalizedPluginsData();
			return null;
		}

		render(
			<ErrorContextProvider>
				<PluginsContextProvider
					fetchingPluging={fetchingPlugins}
					plugins={plugins}
				>
					<ComponentContainingHook />
				</PluginsContextProvider>
			</ErrorContextProvider>
		);

		return returnValue;
	}

	it('returns an empty object if plugins are being fetched', () => {
		const normalizedPluginsData = setup({
			fetchingPlugins: true,
			plugins: [],
		});

		expect(normalizedPluginsData).toStrictEqual({});
	});

	it('returns an object with normalized plugins data', () => {
		const normalizedPluginsData = setup({
			fetchingPlugins: false,
			plugins: [
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
					author_uri:
						'https://github.com/ampproject/amp-wp/graphs/contributors',
					name: {
						raw: 'AMP',
						rendered: '<strong>AMP</strong>',
					},
					plugin: 'amp/amp',
					status: 'active',
					version: '2.2.0-alpha',
				},
			],
		});

		expect(normalizedPluginsData).toStrictEqual({
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
				author_uri:
					'https://github.com/ampproject/amp-wp/graphs/contributors',
				name: 'AMP',
				plugin: 'amp/amp',
				status: 'active',
				slug: 'amp',
				version: '2.2.0-alpha',
			},
		});
	});
});
