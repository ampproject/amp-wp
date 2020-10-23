/**
 * Internal dependencies
 */
import { setImageFromURL } from '../';
import { Mock } from './fixtures/mockClasses';

/**
 * A mock of the onSelect method to pass to setImageFromURL().
 */
class MockSelect extends Mock {
	constructor( ...args ) {
		super( ...args );
		this.onSelect = this.onSelect.bind( this );
	}

	onSelect( data ) {
		this.data = data;
	}
}

describe( 'setImageFromURL', () => {
	it( 'should pass the image data to onSelect()', () => {
		const url = 'https://example.com/an-image.jpeg';
		const id = 2502;
		const width = 1300;
		const height = 1600;
		const mockSelect = new MockSelect();
		const { onSelect } = mockSelect;
		const dispatchImage = () => {};

		setImageFromURL( { url, id, width, height, onSelect, dispatchImage } );
		const data = mockSelect.get( 'data' );

		expect( data.url ).toBe( url );
		expect( data.thumbnail_url ).toBe( url );
		expect( data.attachment_id ).toBe( id );
		expect( data.width ).toBe( width );
		expect( data.height ).toBe( height );
	} );
} );
