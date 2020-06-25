
/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';
import { create } from 'react-test-renderer';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Nav } from '..';
import { NavigationContextProvider } from '../../navigation-context-provider';
import { Options } from '../../options-context-provider';
function OptionsContextProvider( { children } ) {
	return (
		<Options.Provider value={
			{
				options: {},
			}
		}>
			{ children }
		</Options.Provider>
	);
}
OptionsContextProvider.propTypes = {
	children: PropTypes.any,
};

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
				<NavigationContextProvider pages={ testPages }>
					<Nav exitLink="http://site.test" />
				</NavigationContextProvider>
			</OptionsContextProvider>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'hides previous button on first page', () => {
		act( () => {
			render(
				<OptionsContextProvider>
					<NavigationContextProvider pages={ testPages }>
						<Nav exitLink="http://site.test" />
					</NavigationContextProvider>
				</OptionsContextProvider>,
				container,
			);
		} );

		const { nextButton, prevButton } = getNavButtons( container );

		expect( prevButton ).toBeNull();
		expect( nextButton ).not.toBeNull();
	} );

	it( 'disables next button on last page', () => {
		act( () => {
			render(
				<OptionsContextProvider>
					<NavigationContextProvider pages={ [ testPages[ 0 ] ] }>
						<Nav exitLink="http://site.test" />
					</NavigationContextProvider>
				</OptionsContextProvider>,
				container );
		} );

		const { nextButton } = getNavButtons( container );

		expect( nextButton.hasAttribute( 'disabled' ) ).toBe( true );
	} );
} );
