/**
 * Internal dependencies
 */
import { isFileTypeAllowed } from '../';
import { Mock } from './fixtures/mockClasses';

describe( 'isFileTypeAllowed', () => {
	const attachment = new Mock();
	const videoAllowedTypes = [ 'video' ];

	it( 'should return false when the file type is text', () => {
		attachment.set( { type: 'text' } );
		expect( isFileTypeAllowed( attachment, videoAllowedTypes ) ).toBe( false );
	} );

	it( 'should return false when the file type is video but the mime type is video/quicktime', () => {
		attachment.set( { type: 'video', mime: 'video/quicktime' } );
		expect( isFileTypeAllowed( attachment, videoAllowedTypes ) ).toBe( false );
	} );

	it( 'should return true when the file type is image and that is in the allowedTypes', () => {
		const imageAllowedTypes = [ 'image' ];
		attachment.set( { type: 'image' } );
		expect( isFileTypeAllowed( attachment, imageAllowedTypes ) ).toBe( true );
	} );
} );
