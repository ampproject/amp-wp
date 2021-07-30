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
import { DevToolsToggle } from '../';
import { UserContextProvider } from '../../user-context-provider';

jest.mock( '../../user-context-provider' );

let container;

describe( 'DevToolsToggle', () => {
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
			<UserContextProvider>
				<DevToolsToggle />
			</UserContextProvider>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'can be toggled', () => {
		act( () => {
			render(
				<UserContextProvider>
					<DevToolsToggle />
				</UserContextProvider>,
				container,
			);
		} );
		expect( container.querySelector( 'input:checked' ) ).toBeNull();

		act(
			() => {
				container.querySelector( 'input' ).dispatchEvent( new global.MouseEvent( 'click' ) );
			},
		);

		expect( container.querySelector( 'input:checked' ) ).not.toBeNull();

		act(
			() => {
				container.querySelector( 'input' ).dispatchEvent( new global.MouseEvent( 'click' ) );
			},
		);

		expect( container.querySelector( 'input:checked' ) ).toBeNull();
	} );
} );
