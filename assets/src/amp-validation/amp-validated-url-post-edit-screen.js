/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { __, _n, sprintf } from '@wordpress/i18n';

const { ampValidation } = window;

/**
 * The id for the 'Showing x of y errors' notice.
 *
 * @var {string}
 */
const idNumberErrors = 'number-errors';

/**
 * The id for the 'Show all' button.
 *
 * @var {string}
 */
const showAllId = 'show-all-errors';

domReady( () => {
	handleShowAll();
	handleFiltering();
	handleSearching();
	handleStatusChange();
	handleBulkActions();
	changeHeading();
	watchForUnsavedChanges();
	showAMPIconIfEnabled();
} );

let beforeUnloadPromptAdded = false;

/**
 * Add prompt when leaving page due to unsaved changes.
 */
const addBeforeUnloadPrompt = () => {
	if ( beforeUnloadPromptAdded ) {
		return;
	}
	window.addEventListener( 'beforeunload', onBeforeUnload );

	// Remove prompt when clicking trash or update.
	document.querySelector( '#major-publishing-actions' ).addEventListener( 'click', () => {
		window.removeEventListener( 'beforeunload', onBeforeUnload );
	} );

	beforeUnloadPromptAdded = true;
};

/**
 * Watch for unsaved changes.
 *
 * Add an beforeunload warning when attempting to leave the page when there are unsaved changes,
 * unless the user is pressing the trash link or update button.
 */
const watchForUnsavedChanges = () => {
	const onChange = ( event ) => {
		if ( event.target.matches( 'select' ) && event.target.getAttribute( 'id' ) !== 'amp_validation_error_type' ) {
			document.getElementById( 'post' ).removeEventListener( 'change', onChange );
			addBeforeUnloadPrompt();
		}
	};

	document.getElementById( 'post' ).addEventListener( 'change', onChange );
};

/**
 * Show message at beforeunload.
 *
 * @param {Event} event - The beforeunload event.
 * @return {string} Message.
 */
const onBeforeUnload = ( event ) => {
	event.preventDefault();
	event.returnValue = __( 'You have unsaved changes. Are you sure you want to leave?', 'amp' );

	return __( 'You have unsaved changes. Are you sure you want to leave?', 'amp' );
};

/**
 * Updates the <tr> with 'Showing x of y validation errors' at the top of the list table with the current count.
 * If this does not exist yet, it creates the element.
 *
 * @param {number} numberErrorsDisplaying - The number of errors displaying.
 * @param {number} totalErrors - The total number of errors, displaying or not.
 */
const updateShowingErrorsRow = ( numberErrorsDisplaying, totalErrors ) => {
	const showAllButton = document.getElementById( showAllId );
	let thead, th,
		tr = document.getElementById( idNumberErrors );
	const theadQuery = document.getElementsByTagName( 'thead' );

	// Only create the <tr> if it does not exist yet.
	if ( theadQuery[ 0 ] && ! tr ) {
		thead = theadQuery[ 0 ];
		tr = document.createElement( 'tr' );
		th = document.createElement( 'th' );
		th.setAttribute( 'id', idNumberErrors );
		th.setAttribute( 'colspan', '6' );
		tr.appendChild( th );
		thead.appendChild( tr );
	}

	// If all of the errors are displaying, hide the 'Show all' button and the count notice.
	if ( numberErrorsDisplaying === totalErrors ) {
		if ( showAllButton ) {
			showAllButton.classList.add( 'hidden' );
		}
		tr.classList.add( 'hidden' );
	} else if ( null !== numberErrorsDisplaying ) {
		// Update the number of errors displaying and create a 'Show all' button if it does not exist yet.
		document.getElementById( idNumberErrors ).innerText = sprintf(
			/* translators: 1: number of errors being displayed. 2: total number of errors found. */
			_n(
				'Showing %1$s of %2$s validation error',
				'Showing %1$s of %2$s validation errors',
				totalErrors,
				'amp'
			),
			numberErrorsDisplaying,
			totalErrors,
		);
		document.getElementById( idNumberErrors ).classList.remove( 'hidden' );
		conditionallyCreateShowAllButton();
		if ( document.getElementById( showAllId ) ) {
			document.getElementById( showAllId ).classList.remove( 'hidden' );
		}
	}
};

/**
 * Conditionally creates and appends a 'Show all' button.
 */
const conditionallyCreateShowAllButton = () => {
	const buttonContainer = document.getElementById( 'url-post-filter' );
	let showAllButton = document.getElementById( showAllId );

	// There is no 'Show all' <button> yet, but there is a container element for it, create the <button>
	if ( ! showAllButton && buttonContainer ) {
		showAllButton = document.createElement( 'button' );
		showAllButton.id = showAllId;
		showAllButton.classList.add( 'button' );
		showAllButton.innerText = __( 'Show all', 'amp' );

		buttonContainer.appendChild( showAllButton );
	}
};

/**
 * On clicking the 'Show all' <button>, this displays all of the validation errors.
 * Then, it hides this 'Show all' <button> and the notice for the number of errors showing.
 */
const handleShowAll = () => {
	const onClick = ( event ) => {
		if ( ! event.target.matches( '#' + showAllId ) ) {
			return;
		}
		event.preventDefault();

		const validationErrors = document.querySelectorAll( '[data-error-type]' );

		// Iterate through all of the errors, and remove the 'hidden' class.
		validationErrors.forEach( ( element ) => {
			element.parentElement.parentElement.classList.remove( 'hidden' );
		} );

		/*
		 * Update the notice to indicate that all of the errors are displaying.
		 * Like 'Showing 5 of 5 validation errors'.
		 */
		updateShowingErrorsRow( validationErrors.length, validationErrors.length );

		// Hide this 'Show all' button.
		event.target.classList.add( 'hidden' );

		// Change the value of the error type <select> element to 'All Error Types'.
		document.getElementById( 'amp_validation_error_type' ).selectedIndex = 0;
	};

	document.getElementById( 'url-post-filter' ).addEventListener( 'click', onClick );
};

/**
 * Handles filtering by error type, triggered by clicking 'Apply Filter'.
 *
 * Gets the value of the error type <select> element.
 * And hides all <tr> elements that do not have the same type of this value.
 * If 'All Error Types' is selected, this displays all errors.
 */
const handleFiltering = () => {
	const onChange = ( event ) => {
		if ( ! event.target.matches( 'select' ) ) {
			return;
		}

		event.preventDefault();

		const showAllButton = document.getElementById( showAllId );

		const isAllErrorTypesSelected = ( '-1' === event.target.value );
		const errorTypeQuery = document.querySelectorAll( '[data-error-type]' );

		// If the user has chosen 'All Error Types' from the <select>, hide the 'Show all' button.
		if ( isAllErrorTypesSelected && showAllButton ) {
			showAllButton.classList.add( 'hidden' );
		}

		/*
		 * Iterate through all of the <tr> elements in the list table.
		 * If the error type does not match the value (selected error type), hide them.
		 */
		let numberErrorsDisplaying = 0;
		errorTypeQuery.forEach( ( element ) => {
			const errorType = element.getAttribute( 'data-error-type' );

			// If 'All Error Types' was selected, this should display all errors.
			if ( isAllErrorTypesSelected || ! event.target.value || event.target.value === errorType ) {
				element.parentElement.parentElement.classList.remove( 'hidden' );
				numberErrorsDisplaying++;
			} else {
				element.parentElement.parentElement.classList.add( 'hidden' );
			}
		} );

		updateShowingErrorsRow( numberErrorsDisplaying, errorTypeQuery.length );
	};

	document.getElementById( 'amp_validation_error_type' ).addEventListener( 'change', onChange );
};

/**
 * Handles searching for errors via the <input> and the 'Search Errors' <button>.
 */
const handleSearching = () => {
	const onClick = ( event ) => {
		event.preventDefault();
		if ( ! event.target.matches( 'input' ) ) {
			return;
		}

		const searchQuery = document.getElementById( 'invalid-url-search-search-input' ).value;
		const detailsQuery = document.querySelectorAll( 'tbody .column-details' );

		/*
		 * Iterate through the 'Details' column of each row.
		 * If the search query is not present, hide the row.
		 */
		let numberErrorsDisplaying = 0;
		detailsQuery.forEach( ( element ) => {
			let isSearchQueryPresent = false;

			element.querySelectorAll( '.detailed' ).forEach( ( detailed ) => {
				if ( -1 !== detailed.innerText.indexOf( searchQuery ) ) {
					isSearchQueryPresent = true;
				}
			} );

			if ( isSearchQueryPresent ) {
				element.parentElement.classList.remove( 'hidden' );
				numberErrorsDisplaying++;
			} else {
				element.parentElement.classList.add( 'hidden' );
			}
		} );

		updateShowingErrorsRow( numberErrorsDisplaying, detailsQuery.length );
	};

	document.getElementById( 'search-submit' ).addEventListener( 'click', onClick );
};

/**
 * Update icon for select element.
 *
 * @param {HTMLSelectElement} select Select element.
 */
const updateSelectIcon = ( select ) => {
	const newOption = select.options[ select.selectedIndex ];
	if ( newOption ) {
		const iconSrc = newOption.getAttribute( 'data-status-icon' );
		select.parentNode.querySelector( 'img' ).setAttribute( 'src', iconSrc );
	}
};

/**
 * Handles a change in the error status, like from 'New' to 'Accepted'.
 *
 * Gets the data-status-icon value from the newly-selected <option>.
 * And sets this as the src of the status icon <img>.
 */
const handleStatusChange = () => {
	const setRowStatusClass = ( { row, select } ) => {
		const acceptedValue = 3;
		const rejectedValue = 2;
		const status = parseInt( select.options[ select.selectedIndex ].value );

		row.classList.toggle( 'new', isNaN( status ) );
		row.classList.toggle( 'accepted', acceptedValue === status );
		row.classList.toggle( 'rejected', rejectedValue === status );
	};

	const onChange = ( { event, row, select } ) => {
		if ( event.target.matches( 'select' ) ) {
			updateSelectIcon( event.target );
			setRowStatusClass( { row, select } );
		}
	};

	document.querySelectorAll( 'tr[id^="tag-"]' ).forEach( ( row ) => {
		const select = row.querySelector( '.amp-validation-error-status' );

		if ( select ) {
			setRowStatusClass( { row, select } );
			select.addEventListener( 'change', ( event ) => {
				onChange( { event, row, select } );
			} );
		}
	} );
};

/**
 * On checking a bulk action checkbox, this ensures that the 'Accept' and 'Reject' buttons are present. Handle clicking on buttons.
 *
 * They're hidden until one of these boxes is checked.
 * Also, on unchecking the last checked box, this hides these buttons.
 */
const handleBulkActions = () => {
	const acceptButton = document.querySelector( 'button.action.accept' );
	const rejectButton = document.querySelector( 'button.action.reject' );
	const acceptAndRejectContainer = document.getElementById( 'accept-reject-buttons' );

	const onChange = ( event ) => {
		let areThereCheckedBoxes;

		if ( ! event.target.matches( '[type=checkbox]' ) ) {
			return;
		}

		if ( event.target.checked ) {
			// This checkbox was checked, so ensure the buttons display.
			acceptAndRejectContainer.classList.remove( 'hidden' );
		} else {
			/*
			 * This checkbox was unchecked.
			 * So find if there are any other checkboxes that are checked.
			 * If not, hide the 'Accept' and 'Reject' buttons.
			 */
			areThereCheckedBoxes = false;
			document.querySelectorAll( '.check-column [type=checkbox]' ).forEach( ( element ) => {
				if ( element.checked ) {
					areThereCheckedBoxes = true;
				}
			} );
			if ( ! areThereCheckedBoxes ) {
				acceptAndRejectContainer.classList.add( 'hidden' );
			}
		}
	};

	document.querySelectorAll( '.check-column [type=checkbox]' ).forEach( ( element ) => {
		element.addEventListener( 'change', onChange );
	} );

	// Handle click on accept button.
	acceptButton.addEventListener( 'click', () => {
		Array.prototype.forEach.call( document.querySelectorAll( 'select.amp-validation-error-status' ), ( select ) => {
			if ( select.closest( 'tr' ).querySelector( '.check-column input[type=checkbox]' ).checked ) {
				select.value = '3';
				updateSelectIcon( select );
				addBeforeUnloadPrompt();
			}
		} );
	} );

	// Handle click on reject button.
	rejectButton.addEventListener( 'click', () => {
		Array.prototype.forEach.call( document.querySelectorAll( 'select.amp-validation-error-status' ), ( select ) => {
			if ( select.closest( 'tr' ).querySelector( '.check-column input[type=checkbox]' ).checked ) {
				select.value = '2';
				updateSelectIcon( select );
				addBeforeUnloadPrompt();
			}
		} );
	} );
};

/**
 * Changes the page heading and document title, as this doesn't look to be possible with a PHP filter.
 */
const changeHeading = () => {
	const heading = document.querySelector( 'h1.wp-heading-inline' );
	if ( heading && ampValidation.page_heading ) {
		heading.innerText = ampValidation.page_heading;
	}
};

/**
 * Adds the AMP icon to the page heading if AMP is enabled on this URL.
 */
const showAMPIconIfEnabled = () => {
	const heading = document.querySelector( 'h1.wp-heading-inline' );
	if ( heading && true === Boolean( ampValidation.amp_enabled ) ) {
		const ampIcon = document.createElement( 'span' );
		ampIcon.classList.add( 'status-text', 'sanitized' );
		heading.appendChild( ampIcon );
	}
};
