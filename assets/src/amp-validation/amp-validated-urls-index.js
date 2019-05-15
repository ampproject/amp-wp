/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

const CLASS_NAME = 'new';

domReady( () => {
	document.querySelectorAll( 'tr[id^="post-"]' ).forEach( function( row ) {
		if ( row.querySelector( 'span.status-text.' + CLASS_NAME ) ) {
			row.classList.add( CLASS_NAME );
		}
	} );
} );
