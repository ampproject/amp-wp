/**
 * Internal dependencies
 */
import { getTagName } from '../';

describe( 'getTagName', () => {
	it( 'should return type if explicitly set', () => {
		expect( getTagName( { type: 'h2' } ) ).toBe( 'h2' );
	} );

	it( 'should return p if block offset is below threshold', () => {
		expect( getTagName( { positionTop: 200 } ) ).toBe( 'p' );
	} );

	it( 'should return heading if text is large enough', () => {
		expect( getTagName( { fontSize: 'huge' }, true ) ).toBe( 'h1' );
		expect( getTagName( { fontSize: 'huge' }, false ) ).toBe( 'h2' );
		expect( getTagName( { customFontSize: 50 }, true ) ).toBe( 'h1' );
		expect( getTagName( { customFontSize: 50 }, false ) ).toBe( 'h2' );
		expect( getTagName( { fontSize: 'large' }, true ) ).toBe( 'h2' );
		expect( getTagName( { customFontSize: 30 }, true ) ).toBe( 'h2' );
		expect( getTagName( { customFontSize: 30 }, false ) ).toBe( 'h2' );
	} );

	it( 'should return heading if text is short enough', () => {
		expect( getTagName( { content: 'Hello World' }, true ) ).toBe( 'h1' );
		expect( getTagName( { content: 'Hello World' }, false ) ).toBe( 'h2' );
		expect( getTagName( { content: 'This heading is a bit longer' }, true ) ).toBe( 'h2' );
		expect( getTagName( { content: 'This heading is a bit longer' }, false ) ).toBe( 'h2' );
	} );
} );
