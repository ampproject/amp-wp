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
import { RedirectToggle } from '../';
import { OptionsContextProvider } from '../../options-context-provider';

jest.mock( '../../options-context-provider' );

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

		expect( container.querySelector( '.is-checked' ) ).not.toBeNull();

		act(
			() => {
				container.querySelector( 'input' ).dispatchEvent( new global.MouseEvent( 'click' ) );
			},
		);

		expect( container.querySelector( 'input:checked' ) ).toBeNull();

		act(
			() => {
				container.querySelector( 'input' ).dispatchEvent( new global.MouseEvent( 'click' ) );
			},
		);

		expect( container.querySelector( '.is-checked' ) ).not.toBeNull();
	} );
} );
