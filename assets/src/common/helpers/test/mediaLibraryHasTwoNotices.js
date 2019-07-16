/**
 * Internal dependencies
 */
import { mediaLibraryHasTwoNotices } from '../';
import { Mock, AlternateMock } from './fixtures/mockClasses';
import { FILE_TYPE_ERROR_VIEW, FILE_SIZE_ERROR_VIEW } from '../../constants';

describe( 'mediaLibraryHasTwoNotices', () => {
	it( 'should return false when none of the notices appear', () => {
		const mockThis = new Mock();
		mockThis.set( {
			secondary: new AlternateMock(),
		} );

		expect( mediaLibraryHasTwoNotices.call( mockThis ) ).toBe( false );
	} );

	it( 'should return false when only one of the notices appears', () => {
		const mockThis = new Mock();
		const secondary = new AlternateMock();
		secondary.set( FILE_TYPE_ERROR_VIEW, { foo: 'baz' } );
		mockThis.set( { secondary } );

		expect( mediaLibraryHasTwoNotices.call( mockThis ) ).toBe( false );
	} );

	it( 'should return true when both of the notices appear', () => {
		const mockThis = new Mock();
		const secondary = new AlternateMock();
		secondary.set( FILE_TYPE_ERROR_VIEW, { foo: 'baz' } );
		secondary.set( FILE_SIZE_ERROR_VIEW, { bar: 'example' } );
		mockThis.set( { secondary } );

		expect( mediaLibraryHasTwoNotices.call( mockThis ) ).toBe( true );
	} );
} );
