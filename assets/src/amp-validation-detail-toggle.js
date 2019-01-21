/**
 * Localized data
 */
import { detailToggleBtnAriaLabel, sourcesToggleBtnAriaLabel } from 'amp-validation-i18n';

const OPEN_CLASS = 'is-open';

/**
 * Adds detail toggle buttons to the header and footer rows of the validation error "details" column.
 * The buttons are added via JS because there's no easy way to append them to the heading of a sortable
 * table column via backend code.
 *
 * @param {string} containerSelector Selector for elements that will have the button added.
 * @param {string} ariaLabel Screen reader label for the button.
 * @return {Array} Array of added buttons.
 */
function addToggleButtons( containerSelector, ariaLabel ) {
	const addButton = ( container ) => {
		const button = document.createElement( 'button' );
		button.setAttribute( 'aria-label', ariaLabel );
		button.setAttribute( 'type', 'button' );
		button.setAttribute( 'class', 'error-details-toggle' );
		container.appendChild( button );

		return button;
	};

	return [ ...document.querySelectorAll( containerSelector ) ].map( container => addButton( container ) );
}

function addToggleAllListener( { btn, toggleAllButtonSelector = null, targetDetailsSelector } ) {
	let open = false;

	const targetDetails = [ ...document.querySelectorAll( targetDetailsSelector ) ];

	let toggleAllButtons = [];
	if ( toggleAllButtonSelector ) {
		toggleAllButtons = [ ...document.querySelectorAll( toggleAllButtonSelector ) ];
	}

	const onButtonClick = () => {
		open = ! open;
		toggleAllButtons.forEach( toggleAllButton => {
			toggleAllButton.classList.toggle( OPEN_CLASS );
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
 * Adds classes to the rows for the amp_validation_error term list table.
 *
 * This is needed because \WP_Terms_List_Table::single_row() does not allow for additional
 * attributes to be added to the <tr> element.
 */
function addTermListTableRowClasses() {
	const rows = [ ...document.querySelectorAll( '#the-list tr' ) ];
	rows.forEach( ( row ) => {
		const statusText = row.querySelector( '.column-status > .status-text' );
		if ( statusText ) {
			row.classList.toggle( 'new', statusText.classList.contains( 'new' ) );
			row.classList.toggle( 'accepted', statusText.classList.contains( 'accepted' ) );
			row.classList.toggle( 'rejected', statusText.classList.contains( 'rejected' ) );
		}
	} );
}

wp.domReady( () => {
	addToggleButtons( 'th.column-details.manage-column', detailToggleBtnAriaLabel )
		.forEach( ( btn ) => {
			addToggleAllListener( {
				btn,
				toggleAllButtonSelector: '.column-details button.error-details-toggle',
				targetDetailsSelector: '.column-details details'
			} );
		} );

	addToggleButtons( 'th.manage-column.column-sources_with_invalid_output', sourcesToggleBtnAriaLabel )
		.forEach( ( btn ) => {
			addToggleAllListener( {
				btn,
				toggleAllButtonSelector: '.column-sources_with_invalid_output button.error-details-toggle',
				targetDetailsSelector: 'details.source'
			} );
		} );

	addTermListTableRowClasses();
} );
