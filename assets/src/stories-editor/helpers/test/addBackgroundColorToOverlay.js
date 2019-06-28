/**
 * Internal dependencies
 */
import { addBackgroundColorToOverlay } from '../';

describe( 'addBackgroundColorToOverlay', () => {
	it.todo( 'add tests' );

	it( 'uses background color property for a single color', () => {
		const colors = [ { color: '#fff' } ];
		const result = addBackgroundColorToOverlay( {}, colors );

		expect( result ).toMatchObject( { backgroundColor: '#fff' } );
	} );

	it( 'uses linear gradient if there are multiple colors', () => {
		const colors = [ { color: '#fff' }, { color: '#666' }, { color: '#000' } ];
		const result = addBackgroundColorToOverlay( {}, colors );

		expect( result ).toMatchObject( { backgroundImage: 'linear-gradient(to bottom, #fff, #666, #000)' } );
	} );
} );
