/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';

/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * Internal dependencies
 */
import SourcesSummary from '..';
import { PluginsContextProvider } from '../../plugins-context-provider';
import { ThemesContextProvider } from '../../themes-context-provider';

jest.mock( '../../plugins-context-provider' );
jest.mock( '../../themes-context-provider' );

let container;

describe( 'SourcesSummary', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'renders nothing if no sources array is provided', () => {
		act( () => {
			render(
				<SourcesSummary sources={ [] } />,
				container,
			);
		} );

		expect( container.textContent ).toBe( '' );
	} );

	it( 'renders validated theme name if no sources are provided', () => {
		act( () => {
			render(
				<PluginsContextProvider>
					<ThemesContextProvider>
						<SourcesSummary validatedTheme="foo" />
					</ThemesContextProvider>
				</PluginsContextProvider>,
				container,
			);
		} );

		expect( container.innerHTML ).toMatchSnapshot();
		expect( container.textContent ).toContain( 'foo' );
	} );

	it( 'renders a list of sources', () => {
		act( () => {
			render(
				<PluginsContextProvider>
					<ThemesContextProvider>
						<SourcesSummary sources={ [
							{ type: 'plugin', name: 'foo' },
							{ type: 'theme', name: 'bar' },
						] } />
					</ThemesContextProvider>
				</PluginsContextProvider>,
				container,
			);
		} );

		expect( container.innerHTML ).toMatchSnapshot();
		expect( container.textContent ).toContain( 'foo' );
		expect( container.textContent ).toContain( 'bar' );
	} );
} );
