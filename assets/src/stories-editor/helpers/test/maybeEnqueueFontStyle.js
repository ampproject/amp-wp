/**
 * Internal dependencies
 */
import { maybeEnqueueFontStyle } from '../';

describe( 'maybeEnqueueFontStyle', () => {
	it( 'should ignore invalid font name', () => {
		expect( maybeEnqueueFontStyle( undefined ) ).toEqual( undefined );
	} );

	it( 'should ignore missing font name', () => {
		expect( maybeEnqueueFontStyle( 'Tahoma' ) ).toEqual( undefined );
	} );

	it( 'should ignore font without handle', () => {
		expect( maybeEnqueueFontStyle( 'Ubuntu' ) ).toEqual( undefined );
	} );

	it( 'should ignore font without src', () => {
		expect( maybeEnqueueFontStyle( 'Verdana' ) ).toEqual( undefined );
	} );

	it( 'should enqueue requested font only once', () => {
		maybeEnqueueFontStyle( 'Roboto' );
		maybeEnqueueFontStyle( 'Roboto' );

		expect( document.head ).toMatchSnapshot();
	} );
} );
