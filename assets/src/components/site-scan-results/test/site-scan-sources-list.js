/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { act } from 'react-dom/test-utils';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { SiteScanSourcesList } from '../site-scan-sources-list';
import { SiteScan } from '../../site-scan-context-provider';
import scannableUrls from '../data/scannable-urls';

jest.mock( '../../site-scan-context-provider' );

let container;
const Providers = ( { children } ) => {
	return (
		<SiteScan.Provider value={ {
			scannableUrls,
		} }>
			{ children }
		</SiteScan.Provider>
	);
};

Providers.propTypes = {
	children: PropTypes.any,
};

describe( 'SiteScanSourcesList', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'renders a loading spinner if no sources are provided', () => {
		act( () => {
			render(
				<Providers>
					<SiteScanSourcesList sources={ [] } />
				</Providers>,
				container,
			);
		} );

		expect( container.querySelector( '.amp-spinner-container' ) ).not.toBeNull();
	} );

	it( 'renders the correct number of sources', () => {
		act( () => {
			render(
				<Providers>
					<SiteScanSourcesList
						sources={ [
							{ slug: 'foo' },
							{ slug: 'bar' },
						] }
					/>
				</Providers>,
				container,
			);
		} );

		expect( container.querySelectorAll( 'li' ) ).toHaveLength( 2 );
	} );

	it( 'renders active source properties', () => {
		act( () => {
			render(
				<Providers>
					<SiteScanSourcesList
						sources={ [
							{
								author: 'John Doe',
								name: 'Source name',
								slug: 'Source slug',
								status: 'active',
								version: '1.0.0',
							},
						] }
					/>
				</Providers>,
				container,
			);
		} );

		expect( container.querySelector( '.site-scan-results__source-name' ).textContent ).toBe( 'Source name' );
		expect( container.querySelector( '.site-scan-results__source-author' ).textContent ).toBe( 'by John Doe' );
		expect( container.querySelector( '.site-scan-results__source-version' ).textContent ).toBe( 'Version 1.0.0' );
	} );

	it( 'renders active source properties with error detail', () => {
		act( () => {
			render(
				<Providers>
					<SiteScanSourcesList
						sources={ [
							{
								author: 'John Doe',
								name: 'Bad Block',
								slug: 'bad-block',
								status: 'active',
								version: '1.0.0',
							},
						] }
					/>
				</Providers>,
				container,
			);
		} );

		act( () => {
			container.querySelector( '.site-scan-results__sources > li > details' ).click();
		} );

		const sourceDetailTextContent = container.querySelector( '.site-scan-results__source-detail' ).textContent;

		expect( sourceDetailTextContent ).toMatch( /"name": "bad-block"/ );
		expect( sourceDetailTextContent ).not.toMatch( /"name": "wp-includes"/ );
		expect( sourceDetailTextContent ).not.toMatch( /"code": "DISALLOWED_TAG"/ );

		const sourceUrlList = container.querySelector( '.site-scan-results__urls-list' );
		expect( sourceUrlList ).not.toBeNull();
		expect( sourceUrlList.innerHTML ).toMatch( /href="https:\/\/example.org\/"/ );
	} );

	it( 'renders inactive source properties', () => {
		act( () => {
			render(
				<Providers>
					<SiteScanSourcesList
						inactiveSourceNotice="Source is inactive"
						sources={ [ {
							author: 'John Doe',
							name: 'Source name',
							slug: 'Source slug',
							status: 'inactive',
							version: '1.0.0',
						} ] }
					/>
				</Providers>,
				container,
			);
		} );

		expect( container.querySelector( '.site-scan-results__source-name' ).textContent ).toBe( 'Source name' );
		expect( container.querySelector( '.site-scan-results__source-notice' ).textContent ).toBe( 'Source is inactive' );
		expect( container.querySelector( '.site-scan-results__source-author' ) ).toBeNull();
		expect( container.querySelector( '.site-scan-results__source-version' ) ).toBeNull();
	} );

	it( 'renders uninstalled source properties', () => {
		act( () => {
			render(
				<Providers>
					<SiteScanSourcesList
						uninstalledSourceNotice="Source is uninstalled"
						sources={ [ {
							slug: 'Source slug',
							status: 'uninstalled',
						} ] }
					/>
				</Providers>,
				container,
			);
		} );

		expect( container.querySelector( '.site-scan-results__source-slug' ).textContent ).toBe( 'Source slug' );
		expect( container.querySelector( '.site-scan-results__source-notice' ).textContent ).toBe( 'Source is uninstalled' );
		expect( container.querySelector( '.site-scan-results__source-name' ) ).toBeNull();
		expect( container.querySelector( '.site-scan-results__source-author' ) ).toBeNull();
		expect( container.querySelector( '.site-scan-results__source-version' ) ).toBeNull();
	} );
} );
