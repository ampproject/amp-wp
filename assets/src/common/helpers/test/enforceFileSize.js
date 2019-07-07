/**
 * Internal dependencies
 */
import { enforceFileSize } from '../';
import { Mock, AlternateMock, MockSelectionError } from './fixtures/mockClasses';

const fileSizeError = 'select-file-size-error';

describe( 'enforceFileSize', () => {
	it( 'should have a new error when the video file size is too big', () => {
		const mockThis = new Mock();
		const selectButton = { model: new AlternateMock() };
		mockThis.set( {
			secondary: new AlternateMock(),
			select: selectButton,
		} );

		const attachment = new Mock();
		const filesize = 12000000;
		const length = 4;
		attachment.set( {
			media_details: { filesize, length },
			media_type: 'video',
		} );

		enforceFileSize.call( mockThis, attachment, MockSelectionError );
		const actualSelectionError = mockThis.secondary.get( fileSizeError );

		expect( actualSelectionError.get( 'maxVideoMegabytesPerSecond' ) ).toBe( 1 );
		expect( actualSelectionError.get( 'actualVideoMegabytesPerSecond' ) ).toBe( 3 );

		// This shouldn't disable the 'Select' button in the Media Library.
		expect( selectButton.model.get( 'disabled' ) ).toBe( undefined );
	} );

	it( 'should not have an error when the video file size is under the maximum', () => {
		const mockThis = new Mock();
		const selectButton = { model: new AlternateMock() };
		mockThis.set( {
			secondary: new AlternateMock(),
			select: selectButton,
		} );

		const attachment = new Mock();
		const filesize = 6000000;
		const length = 6;
		attachment.set( {
			media_details: { filesize, length },
			media_type: 'video',
		} );

		enforceFileSize.call( mockThis, attachment, MockSelectionError );

		expect( mockThis.secondary.get( fileSizeError ) ).toBe( undefined );
		expect( selectButton.model.get( 'disabled' ) ).toBe( undefined );
	} );

	it( 'should not have an error if the file type is not a video', () => {
		const mockThis = new Mock();
		const selectButton = { model: new AlternateMock() };
		mockThis.set( {
			secondary: new AlternateMock(),
			select: selectButton,
		} );

		const attachment = new Mock();
		const filesize = 12000000;
		const length = 4;
		const nonVideo = 'image';
		attachment.set( {
			media_details: { filesize, length },
			media_type: nonVideo,
		} );

		enforceFileSize.call( mockThis, attachment, MockSelectionError );

		expect( mockThis.secondary.get( fileSizeError ) ).toBe( undefined );
		expect( selectButton.model.get( 'disabled' ) ).toBe( undefined );
	} );
} );
