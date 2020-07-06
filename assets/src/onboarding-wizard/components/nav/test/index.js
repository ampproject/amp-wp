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
import { UserContextProvider } from '../../user-context-provider';
import { OptionsContextProvider } from '../../../../components/options-context-provider';

jest.mock( '../../../../components/options-context-provider' );
jest.mock( '../../user-context-provider' );

let container;

const getNavButtons = ( containerElement ) => ( {
	nextButton: containerElement.querySelector( '.onboarding-wizard-nav__prev-next button.is-primary' ),
	prevButton: containerElement.querySelector( '.onboarding-wizard-nav__prev-next button:not(.is-primary)' ),
} );

const MyPageComponent = () => <div />;
const testPages = [
	{ PageComponent: MyPageComponent, slug: 'slug', title: 'Page 0' },
	{ PageComponent: MyPageComponent, slug: 'slug-2', title: 'Page 1' },
];

const Providers = ( { children, pages } ) => (
	<OptionsContextProvider>
		<UserContextProvider>
			<NavigationContextProvider pages={ pages }>
				{ children }
			</NavigationContextProvider>
		</UserContextProvider>
	</OptionsContextProvider>
);
Providers.propTypes = {
	children: PropTypes.any,
	pages: PropTypes.array,
};

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
			<Providers pages={ testPages }>
				<Nav closeLink="http://site.test/wp-admin" finishLink="http://site.test" />
			</Providers>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'hides previous button on first page', () => {
		act( () => {
			render(
				<Providers pages={ testPages }>
					<Nav closeLink="http://site.test/wp-admin" finishLink="http://site.test" />
				</Providers>,
				container,
			);
		} );

		const { nextButton, prevButton } = getNavButtons( container );

		expect( prevButton ).toBeNull();
		expect( nextButton ).not.toBeNull();
	} );

	it( 'changes next button to "Finish" on last page', () => {
		act( () => {
			render(
				<Providers pages={ testPages }>
					<Nav closeLink="http://site.test/wp-admin" finishLink="http://site.test" />
				</Providers>,
				container,
			);
		} );

		const { nextButton } = getNavButtons( container );

		expect( nextButton.textContent ).toBe( 'Next' );

		act( () => {
			nextButton.dispatchEvent( new global.MouseEvent( 'click', { bubbles: true } ) );
		} );

		expect( nextButton.textContent ).toBe( 'Finish' );
	} );

	it( 'close button hides on last page', () => {
		act( () => {
			render(
				<Providers pages={ testPages }>
					<Nav closeLink="http://site.test/wp-admin" finishLink="http://site.test" />
				</Providers>,
				container,
			);
		} );

		const { nextButton } = getNavButtons( container );
		let closeButton = container.querySelector( '.onboarding-wizard-nav__close a' );

		expect( closeButton ).not.toBeNull();

		act( () => {
			nextButton.dispatchEvent( new global.MouseEvent( 'click', { bubbles: true } ) );
		} );

		closeButton = container.querySelector( '.onboarding-wizard-nav__close a' );
		expect( closeButton ).toBeNull();
	} );
} );
