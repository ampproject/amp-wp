
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

	it( 'has main elements and responds to clicks', () => {
		let centeredItem = itemsContainer.querySelector( 'li:nth-of-type(3)' );
		const namespace = 'my-carousel';
		const items = itemsContainer.querySelectorAll( 'li' );

		act( () => {
			render(
				<CarouselNav
					centeredItem={ centeredItem }
					items={ items }
					namespace={ namespace }
					setCenteredItem={ ( newItem ) => {
						centeredItem = newItem;
					} }
					highlightedItemIndex={ 5 }
					showDots={ true }
				/>,
				container,
			);
		} );

		expect( container.querySelector( '#my-carousel__prev-button:not(:disabled)' ) ).not.toBeNull();
		expect( container.querySelector( '#my-carousel__next-button:not(:disabled)' ) ).not.toBeNull();

		act( () => {
			container.querySelector( `#${ namespace }__${ items[ 0 ].id }-dot` ).click();
		} );

		expect( centeredItem.id ).toBe( 'item-0' );
		expect(	container.querySelector( `#${ namespace }__${ items[ 5 ].id }-dot` ).classList.contains( `${ namespace }__nav-dot-button--active` ) ).toBe( true );
	} );
} );
