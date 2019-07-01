/**
 * Internal dependencies
 */
import { getAmpFitTextContent } from '../';

describe( 'getAmpFitTextContent', () => {
	it( 'should extract the element\'s content', () => {
		const result = getAmpFitTextContent( '<amp-fit-text>Hello World</amp-fit-text>' );
		expect( result ).toBe( 'Hello World' );
	} );

	it( 'should extract the element\'s content even when there are some attributes', () => {
		const result = getAmpFitTextContent( '<amp-fit-text width="1" height="2" layout="responsive">Hello World</amp-fit-text>' );
		expect( result ).toBe( 'Hello World' );
	} );
} );
