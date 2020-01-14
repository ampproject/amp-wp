/**
 * Internal dependencies
 */
import { setupReducer } from './_utils';

describe( 'updateStory', () => {
	it( 'should update story with given properties', () => {
		const { updateStory } = setupReducer();

		const result = updateStory( { properties: { a: 1, b: 2 } } );

		expect( result.story ).toStrictEqual( { a: 1, b: 2 } );
	} );

	it( 'should overwrite existing properties but not delete old ones', () => {
		const { updateStory } = setupReducer();

		const firstResult = updateStory( { properties: { a: 1, b: 2 } } );
		expect( firstResult.story ).toStrictEqual( { a: 1, b: 2 } );

		const secondResult = updateStory( { properties: { b: 3, c: 4 } } );
		expect( secondResult.story ).toStrictEqual( { a: 1, b: 3, c: 4 } );
	} );
} );
