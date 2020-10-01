
/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { CarouselNav } from '../carousel-nav';

let container;
let itemsContainer;

describe( 'CarouselNav', () => {
	beforeEach( () => {
		itemsContainer = document.createElement( 'ul' );
		for ( let i = 0; i < 10; i += 1 ) {
			const item = document.createElement( 'li' );
			item.innerText = `item ${ i }`;
			item.setAttribute( 'data-label', `item ${ i }` );
			item.setAttribute( 'id', `item-${ i }` );
			itemsContainer.appendChild( item );
		}

		container = document.createElement( 'div' );
		document.body.appendChild( itemsContainer );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( itemsContainer );
		document.body.removeChild( container );
		itemsContainer = null;
		container = null;
	} );

	it( 'has main elements', () => {
		let currentPage = itemsContainer.querySelector( 'li:nth-of-type(3)' );
		const namespace = 'my-carousel';
		const items = itemsContainer.querySelectorAll( 'li' );

		act( () => {
			render(
				<CarouselNav
					currentPage={ currentPage }
					items={ items }
					namespace={ namespace }
					nextButtonDisabled={ false }
					prevButtonDisabled={ false }
					setCurrentPage={ ( newItem ) => {
						currentPage = newItem;
					} }
					centeredItemIndex={ 5 }
					showDots={ true }
				/>,
				container,
			);
		} );

		expect( container.querySelector( '#my-carousel__prev-button:not(:disabled)' ) ).not.toBeNull();
		expect( container.querySelector( '#my-carousel__next-button:not(:disabled)' ) ).not.toBeNull();
	} );
} );
