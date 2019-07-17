/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { maybeSupplyPoster } from '../';
import { Mock } from './fixtures/mockClasses';

/**
 * Sets the component's attributes.
 *
 * @param {Object} attributes The attributes to set.
 */
const setAttributes = function( attributes ) {
	for ( const attributeName in attributes ) {
		if ( attributes.hasOwnProperty( attributeName ) ) {
			this.props.attributes[ attributeName ] = attributes[ attributeName ];
		}
	}
};

describe( 'maybeSupplyPoster', () => {
	it( 'it should not attempt to set the poster if the media argument is falsey', () => {
		const mockThis = new Component();
		const media = null;
		maybeSupplyPoster.call( mockThis, media );

		expect( mockThis.props ).toBe( undefined );
	} );

	it( 'should not attempt to set the poster if the media only has the default video image', () => {
		const mockThis = new Component();
		const media = new Mock();
		const src = 'https://example.com/wp-includes/images/media/video.png';
		media.set( {
			image: { src },
		} );
		maybeSupplyPoster.call( mockThis, media );

		expect( mockThis.props ).toBe( undefined );
	} );

	it( 'should set the poster if the media has an image that is not the default', () => {
		const mockThis = new Component();
		mockThis.props = {
			attributes: {},
			setAttributes: setAttributes.bind( mockThis ),
		};

		const media = new Mock();
		const src = 'https://example.com/wp-content/uploads/2019/10/baz-video-poster.png';
		media.set( {
			image: { src },
		} );
		maybeSupplyPoster.call( mockThis, media );

		expect( mockThis.props.attributes.poster ).toBe( src );
	} );
} );
