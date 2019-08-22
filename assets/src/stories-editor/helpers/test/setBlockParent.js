/**
 * Internal dependencies
 */
import { setBlockParent } from '../';

describe( 'setBlockParent', () => {
	it.todo( 'add tests' );

	it( 'does not change the parent for the page block', () => {
		const result = setBlockParent( { name: 'amp/amp-story-page' } );

		expect( result ).toStrictEqual( { name: 'amp/amp-story-page' } );
	} );

	it( 'sets an empty parent for reusable blocks', () => {
		const coreBlock = setBlockParent( { name: 'core/block' } );
		const templateBlock = setBlockParent( { name: 'core/template' } );

		expect( coreBlock ).toMatchObject( { parent: [ '' ] } );
		expect( templateBlock ).toMatchObject( { parent: [ '' ] } );
	} );

	it( 'forces other blocks to be a child of the page block', () => {
		const codeBlock = setBlockParent( { name: 'core/code' } );
		const textBlock = setBlockParent( { name: 'amp/amp-story-text' } );

		expect( codeBlock ).toMatchObject( { parent: [ 'amp/amp-story-page' ] } );
		expect( textBlock ).toMatchObject( { parent: [ 'amp/amp-story-page' ] } );
	} );
} );
