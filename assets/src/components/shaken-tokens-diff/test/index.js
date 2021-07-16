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
import ShakenTokensDiff from '..';
import Declaration from '../declaration';

let container;

describe( 'ShakenTokensDiff', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'renders nothing if no tokens are provided', () => {
		act( () => {
			render(
				<ShakenTokensDiff tokens={ null } />,
				container,
			);
		} );

		expect( container.textContent ).toBe( '' );
	} );

	describe( 'Declaration', () => {
		it( 'renders inserted token', () => {
			act( () => {
				render(
					<Declaration
						token={ [
							true,
							{
								'.foo': true,
								'.bar': false,
							},
							[
								'margin:0',
								'padding:0',
							],
						] }
					/>,
					container,
				);
			} );

			expect( container.querySelector( '.declaration-block' ) ).not.toBeNull();

			expect( container.querySelectorAll( 'ins' ) ).toHaveLength( 2 );
			expect( container.querySelectorAll( 'del' ) ).toHaveLength( 1 );

			expect( container.querySelector( 'ins.selector' ) ).not.toBeNull();
			expect( container.querySelector( 'ins.selector' ).textContent ).toBe( '.foo,' );

			expect( container.querySelector( 'del.selector' ) ).not.toBeNull();
			expect( container.querySelector( 'del.selector' ).textContent ).toBe( '.bar' );

			expect( container.querySelector( 'ins:last-child' ).textContent ).toBe( '	{ margin:0; padding:0 }' );
		} );

		it( 'renders removed token', () => {
			act( () => {
				render(
					<Declaration
						token={ [
							false,
							{
								html: false,
							},
							[
								'-o-object-fit:contain',
							],
						] }
					/>,
					container,
				);
			} );

			expect( container.querySelector( '.declaration-block' ) ).not.toBeNull();
			expect( container.querySelectorAll( 'del' ) ).toHaveLength( 2 );
			expect( container.querySelectorAll( 'del.selector' ) ).toHaveLength( 1 );
			expect( container.querySelector( 'del.selector' ).textContent ).toBe( 'html' );
			expect( container.querySelector( 'del:last-child' ).textContent ).toBe( '	{ -o-object-fit:contain }' );
		} );

		it( 'renders a removed selector without a declaration', () => {
			act( () => {
				render(
					<Declaration
						token={ [
							false,
							'@supports ((position:-webkit-sticky) or (position:sticky)){',
						] }
					/>,
					container,
				);
			} );

			expect( container.querySelector( '.declaration-block' ) ).not.toBeNull();
			expect( container.querySelectorAll( 'del.selector' ) ).toHaveLength( 1 );
			expect( container.querySelector( 'del.selector' ).textContent ).toBe( '@supports ((position:-webkit-sticky) or (position:sticky)){' );
		} );

		it( 'respects the indentation level', () => {
			act( () => {
				render(
					<Declaration
						indentation={ 2 }
						token={ [
							false,
							{
								html: false,
							},
							[
								'-o-object-fit:contain',
							],
						] }
					/>,
					container,
				);
			} );

			expect( container.querySelector( 'del.selector' ).textContent ).toBe( '		html' );
			expect( container.querySelector( 'del:last-child' ).textContent ).toBe( '			{ -o-object-fit:contain }' );
		} );

		it( 'adds help text as ABBR elements to selectors', () => {
			const token = [
				true,
				{
					':root:not(#_):not(#_) .foo': true,
					'html:not(#_) .amp-wp-bar': true,
				},
				[
					'display:block',
				],
			];

			act( () => {
				render(
					<Declaration isStyleAttribute={ false } token={ token } />,
					container,
				);
			} );
			expect( container.querySelector( 'ins.selector:nth-child(1)' ).textContent ).toBe( ':root:not(#_):not(#_) .foo,' );
			expect( container.querySelector( 'ins.selector:nth-child(2)' ).textContent ).toBe( 'html:not(#_) .amp-wp-bar' );
			expect( container.querySelector( 'ins.selector:nth-child(1) abbr' ) ).not.toBeNull();
			expect( container.querySelector( 'ins.selector:nth-child(1) abbr' ).title ).toBe( 'Selector generated to increase specificity so the cascade is preserved for properties moved from style attribute to CSS rules in style[amp-custom].' );

			expect( container.querySelectorAll( 'ins.selector:nth-child(2) abbr' ) ).toHaveLength( 1 );

			act( () => {
				render(
					<Declaration isStyleAttribute={ true } token={ token } />,
					container,
				);
			} );
			expect( container.querySelector( 'ins.selector:nth-child(1) abbr' ).title ).toBe( 'Selector generated to increase specificity for important properties so that the CSS cascade is preserved. AMP does not allow important properties.' );
			expect( container.querySelector( 'ins.selector:nth-child(2) abbr:nth-child(2)' ) ).not.toBeNull();
			expect( container.querySelector( 'ins.selector:nth-child(2) abbr:nth-child(2)' ).title ).toBe( 'Class name generated during extraction of inline style to style[amp-custom].' );
		} );
	} );
} );
