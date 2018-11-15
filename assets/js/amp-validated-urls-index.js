/* exported ampValidatedUrlsIndex */

const ampValidatedUrlsIndex = ( function() { // eslint-disable-line no-unused-vars
	let component = {
		classes: {}
	};

	/**
	 * The class for the new status
	 *
	 * @type {string}
	 */
	component.classes.new = 'new';

	/**
	 * Boot.
	 */
	component.boot = function boot() {
		component.highlightRowsWithNewStatus();
	};

	/**
	 * Highlight rows with new status.
	 */
	component.highlightRowsWithNewStatus = function highlightRowsWithNewStatus() {
		document.querySelectorAll( 'tr[id^="post-"]' ).forEach( function( row ) {
			if ( row.querySelector( 'span.status-text.' + component.classes.new ) ) {
				row.classList.add( 'new' );
			}
		} );
	};

	return component;
}() );
