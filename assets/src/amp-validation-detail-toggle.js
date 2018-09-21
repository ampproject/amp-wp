/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Localized data
 */
import { detailToggleBtnAriaLabel, sourcesToggleBtnAriaLabel } from 'amp-validation-i18n';

const OPEN_CLASS = 'is-open';

/**
 * Adds detail toggle buttons to the header and footer rows of the validation error "details" column.
 * The buttons are added via JS because there's no easy way to append them to the heading of a sortable
 * table column via backend code.
 */
function addToggleButtons( containerSelector ) {
	const addButton = ( container ) => {
		const button = document.createElement( 'button' );
		button.setAttribute( 'aria-label', detailToggleBtnAriaLabel );
		button.setAttribute( 'type', 'button' );
		button.setAttribute( 'class', 'error-details-toggle' );
		container.appendChild( button );

		return button;
	};

	return [ ...document.querySelectorAll( containerSelector ) ].map( container =>  addButton( container ) );

	[ ...document.querySelectorAll( 'th.manage-column.column-sources_with_invalid_output' ) ].forEach( th => {
		addButtons( { th, targetDetailSelector: 'details.source' } );
	} );
}

function addToggleAllDetailsButton( { btn, targetButtonSelector = null, targetDetailsSelector } ) {
	let open = false;

	const targetDetails = [ ...document.querySelectorAll( targetDetailsSelector ) ];

	let targetButtons = [];
	if ( targetButtonSelector ) {
		targetButtons = [ ...document.querySelectorAll( targetButtonSelector ) ];
	}

	const onButtonClick = () => {
		open = ! open;
		targetButtons.forEach( targetButton => {
			targetButton.classList.toggle( OPEN_CLASS );
		} );

		targetDetails.forEach( detail => {
			if ( open ) {
				detail.setAttribute( 'open', true );
			} else {
				detail.removeAttribute( 'open' );
			}
		} );
	};

	btn.addEventListener( 'click', onButtonClick );

}

/**
 * Adds a listener toggling all details in the error type taxonomy details column.
 */
function _addToggleAllListener( toggleSelector ) {
	let open = false;

	const details = [ ...document.querySelectorAll( '.column-details details, .column-sources_with_invalid_output details' ) ];
	const toggleButtons = [ ...document.querySelectorAll( 'button.error-details-toggle' ) ];
	const onButtonClick = ( button ) => {
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
			onButtonClick( event.target );
		}
	} );
}

domReady( () => {
	addToggleButtons( '.th.column-details.manage-column' )
		.forEach( ( btn ) => {
			addToggleAllDetailsButton( {
				btn,
				targetButtonSelector: '.manage-column button.error-details-toggle',
				targetDetailsSelector: '.column-details details'
			} );
		} );
//	addToggleAllListener( );
} );
