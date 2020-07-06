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
import { RedirectToggle } from '../redirect-toggle';
import { OptionsContextProvider } from '../../../../components/options-context-provider';

jest.mock( '../../../../components/options-context-provider' );

let container;

describe( 'RedirectToggle', () => {
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
				<RedirectToggle />
			</OptionsContextProvider>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'can be toggled', () => {
		act( () => {
			render(
				<OptionsContextProvider>
					<RedirectToggle />
				</OptionsContextProvider>,
				container,
			);
		} );

		const toggle = container.querySelector( '.components-form-toggle' );
		expect( [ ...toggle.classList ] ).toContain( 'is-checked' );
	} );
} );
