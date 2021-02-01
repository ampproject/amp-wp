/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';
import { create } from 'react-test-renderer';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { AMPNotice, NOTICE_TYPE_SUCCESS, NOTICE_SIZE_LARGE, NOTICE_TYPE_ERROR, NOTICE_SIZE_SMALL, NOTICE_TYPE_INFO } from '..';

let container;

describe( 'AMPNotice', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'matches snapshots', () => {
		let wrapper = create(
			<AMPNotice type={ NOTICE_TYPE_SUCCESS } size={ NOTICE_SIZE_LARGE }>
				{ 'Component children' }
			</AMPNotice>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();

		wrapper = create(
			<AMPNotice
				type={ NOTICE_TYPE_ERROR }
				size={ NOTICE_SIZE_SMALL }>
				{ 'Component children' }
			</AMPNotice>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'has correct classes', () => {
		act( () => {
			render(
				<AMPNotice>
					{ 'children' }
				</AMPNotice>,
				container,
			);
		} );

		expect( container.querySelector( 'div' ).getAttribute( 'class' ) ).toBe( 'amp-notice amp-notice--info amp-notice--large' );

		act( () => {
			render(
				<AMPNotice type={ NOTICE_TYPE_SUCCESS } size={ NOTICE_SIZE_LARGE } className="my-cool-class">
					{ 'children' }
				</AMPNotice>,
				container,
			);
		} );

		expect( container.querySelector( 'div' ).getAttribute( 'class' ) ).toBe( 'my-cool-class amp-notice amp-notice--success amp-notice--large' );

		act( () => {
			render(
				<AMPNotice type={ NOTICE_TYPE_INFO } size={ NOTICE_SIZE_SMALL }>
					{ 'children' }
				</AMPNotice>,
				container,
			);
		} );

		expect( container.querySelector( 'div' ).getAttribute( 'class' ) ).toBe( 'amp-notice amp-notice--info amp-notice--small' );
	} );
} );
