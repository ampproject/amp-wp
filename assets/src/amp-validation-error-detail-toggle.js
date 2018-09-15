/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Localized data
 */
import { btnAriaLabel } from 'amp-validation-i18n';

const OPEN_CLASS = 'is-open';

/**
 * Adds detail toggle buttons to the header and footer rows of the validation error "details" column.
 * The buttons are added via JS because there's no easy way to append them to the heading of a sortable
 * table column via backend code.
 */
function addToggleButtons() {
	[ ...document.querySelectorAll( 'th.column-details.manage-column' ) ].forEach( th => {
		const button = document.createElement( 'button' );
		button.setAttribute( 'aria-label', btnAriaLabel );
		button.setAttribute( 'type', 'button' );
		button.setAttribute( 'class', 'error-details-toggle' );
		th.appendChild( button );
	} );
}

/**
 * Adds a listener toggling all details in the error type taxonomy details column.
 */
function addToggleListener() {
	let open = false;

	const details = [ ...document.querySelectorAll( '.column-details details' ) ];
	const toggleButtons = [ ...document.querySelectorAll( 'button.error-details-toggle' ) ];
	const onButtonClick = () => {
		open = ! open;
		toggleButtons.forEach( btn => {
			btn.classList.toggle( OPEN_CLASS );
		} );
		details.forEach( detail => {
			if ( open ) {
				detail.setAttribute( 'open', true );
			} else {
				detail.removeAttribute( 'open' );
			}
		} );
	};

	window.addEventListener( 'click', event => {
		if ( toggleButtons.includes( event.target ) ) {
			onButtonClick();
		}
	} );
}

/**
 * Adds classes to the rows for the amp_validation_error term list table.
 *
 * This is needed because \WP_Terms_List_Table::single_row() does not allow for additional
 * attributes to be added to the <tr> element.
 */
function addTermListTableRowClasses() {
	const rows = [ ...document.querySelectorAll( '#the-list > tr' ) ];
	rows.forEach( ( row ) => {
		const statusText = row.querySelector( '.column-status > .status-text' );
		if ( statusText ) {
			row.classList.toggle( 'new', statusText.classList.contains( 'new' ) );
			row.classList.toggle( 'accepted', statusText.classList.contains( 'accepted' ) );
			row.classList.toggle( 'rejected', statusText.classList.contains( 'rejected' ) );
		}
	} );
}

domReady( () => {
	addToggleButtons();
	addToggleListener();
	addTermListTableRowClasses();
} );
