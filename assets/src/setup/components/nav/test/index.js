
/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';
import { create } from 'react-test-renderer';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Nav } from '..';
import { NavigationContextProvider } from '../../navigation-context-provider';
import { OptionsContextProvider } from '../../options-context-provider';
import { UserContextProvider } from '../../user-context-provider';

jest.mock( '../../options-context-provider' );
jest.mock( '../../user-context-provider' );

let container;

const getNavButtons = ( containerElement ) => ( {
	nextButton: containerElement.querySelector( '.amp-setup-nav__prev-next button.is-primary' ),
	prevButton: containerElement.querySelector( '.amp-setup-nav__prev-next button:not(.is-primary)' ),
} );

const MyPageComponent = () => <div />;
const testPages = [ { PageComponent: MyPageComponent, slug: 'slug', title: 'Page 0' }, { PageComponent: MyPageComponent, slug: 'slug-2', title: 'Page 1' } ];

describe( 'Nav', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'matches snapshot', () => {
		const wrapper = create(
			<OptionsContextProvider>
				<UserContextProvider>
					<NavigationContextProvider pages={ testPages }>
						<Nav exitLink="http://site.test" />
					</NavigationContextProvider>
				</UserContextProvider>
			</OptionsContextProvider>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'hides previous button on first page', () => {
		act( () => {
			render(
				<OptionsContextProvider>
					<UserContextProvider>
						<NavigationContextProvider pages={ testPages }>
							<Nav exitLink="http://site.test" />
						</NavigationContextProvider>
					</UserContextProvider>
				</OptionsContextProvider>,
				container,
			);
		} );

		const { nextButton, prevButton } = getNavButtons( container );

		expect( prevButton ).toBeNull();
		expect( nextButton ).not.toBeNull();
	} );
} );
