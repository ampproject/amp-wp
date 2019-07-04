/**
 * Internal dependencies
 */
import { isFileTypeAllowed } from '../';

class MockAttachment {
	get( key ) {
		return this[ key ];
	}

	set( values ) {
		for ( const property in values ) {
			if ( values.hasOwnProperty( property ) ) {
				this[ property ] = values[ property ];
			}
		}
	}
}

describe( 'isFileTypeAllowed', () => {
	const attachment = new MockAttachment();
	const videoAllowedTypes = [ 'video' ];

	it( 'should return false when the file type is text', () => {
		attachment.set( { type: 'text' } );
		expect( isFileTypeAllowed( attachment, videoAllowedTypes ) ).toBe( false );
	} );

	it( 'should return false when the file type is video but the mime type is video/quicktime', () => {
		attachment.set( { type: 'video', mime: 'video/quicktime' } );
		expect( isFileTypeAllowed( attachment, videoAllowedTypes ) ).toBe( false );
	} );

	it( 'should return true when the file type is video and the mime type is correct', () => {
		attachment.set( { type: 'video', mime: 'video/mp4' } );
		expect( isFileTypeAllowed( attachment, videoAllowedTypes ) ).toBe( true );
	} );

	it( 'should return true when the file type is image and that is in the allowedTypes', () => {
		attachment.set( { type: 'image' } );
		const imageAllowedTypes = [ 'image' ];
		expect( isFileTypeAllowed( attachment, imageAllowedTypes ) ).toBe( true );
	} );
} );
