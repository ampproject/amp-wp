/**
 * WordPress dependencies
 */
import { sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { getNoticeTemplate } from '../';

describe( 'getNoticeTemplate', () => {
	const message = 'This is an example message';
	const template = getNoticeTemplate( message );
	const type = typeof template;

	it( 'should have the proper type', () => {
		expect( type ).toBe( 'function' );
	} );
	it( 'should return the correct message', () => {
		expect( template() ).toBe( sprintf( '<p>%s</p>', message ) );
	} );
} );
