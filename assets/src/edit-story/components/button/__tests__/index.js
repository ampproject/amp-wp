/**
 * External dependencies
 */
import { render } from '@testing-library/react';
import 'jest-styled-components';

/**
 * Internal dependencies
 */
import theme from '../../../theme.js';
import * as buttons from '../index.js';
import UndoIcon from '../icon_undo.svg';
import RedoIcon from '../icon_redo.svg';

const testDesc = {
	Primary: 'contain a modified color, border-color and background-color',
	Secondary: 'contain a modified color, border-color and background-color',
	Outline: 'contain a modified color and border-color',
	Undo: 'contain the undo svg icon',
	Redo: 'contain the redo svg icon',
};

// Loop over the buttons and run specific tests for each.
for ( const button in buttons ) {
	if ( buttons.hasOwnProperty( button ) ) { // eslint-disable-line import/namespace
		const Component = buttons[ button ];

		const { container } = render( <Component theme={ theme } /> );
		const btn = container.firstChild;

		describe( 'button', () => {
			it( `should match snapshot and ${ testDesc[ button ] }`, () => {
				expect( btn ).toMatchSnapshot();

				switch ( button ) {
					case 'Primary':
						expect( btn ).toHaveStyleRule( 'border-color', theme.colors.action );
						expect( btn ).toHaveStyleRule( 'background-color', theme.colors.action );
						expect( btn ).toHaveStyleRule( 'color', theme.colors.fg.v1 );

						break;

					case 'Secondary':
						expect( btn ).toHaveStyleRule( 'border-color', theme.colors.fg.v1 );
						expect( btn ).toHaveStyleRule( 'background-color', theme.colors.fg.v3 );
						expect( btn ).toHaveStyleRule( 'color', theme.colors.bg.v5 );

						break;

					case 'Outline':
						expect( btn ).toHaveStyleRule( 'border-color', theme.colors.fg.v2 );
						expect( btn ).toHaveStyleRule( 'color', theme.colors.fg.v1 );

						break;

					case 'Undo':
						expect( btn.innerHTML ).toStrictEqual( render( <UndoIcon /> ).container.innerHTML );

						break;

					case 'Redo':
						expect( btn.innerHTML ).toStrictEqual( render( <RedoIcon /> ).container.innerHTML );
						break;

					default:
						break;
				}
			} );
		} );
	}
}
