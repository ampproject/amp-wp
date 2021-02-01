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
import { ConditionalDetails } from '..';

/**
 * Internal dependencies
 */

let container;

describe( 'ConditionalDetails', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'renders as expected', () => {
		let wrapper = create(
			<ConditionalDetails summary={ (
				<div>
					{ 'Summary' }
				</div>
			) }>
				<div>
					{ 'children' }
				</div>
			</ConditionalDetails>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();

		wrapper = create(
			<ConditionalDetails summary={ (
				<div>
					{ 'Summary' }
				</div>
			) }
			>
				{ [ null, null ] }
			</ConditionalDetails>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'has correct classes', () => {
		act( () => {
			render(
				<ConditionalDetails summary={ (
					<div>
						{ 'Summary' }
					</div>
				) }>
					{ 'children' }
				</ConditionalDetails>,
				container,
			);
		} );

		expect( container.querySelector( 'details' ) ).not.toBeNull();
		expect( container.querySelector( 'summary' ) ).not.toBeNull();

		act( () => {
			render(
				<ConditionalDetails summary={ (
					<div>
						{ 'Summary' }
					</div>
				) }
				>
					{ [ null, null ] }
				</ConditionalDetails>,
				container,
			);
		} );

		expect( container.querySelector( 'summary' ) ).toBeNull();
		expect( container.querySelector( 'details' ) ).toBeNull();
	} );
} );
