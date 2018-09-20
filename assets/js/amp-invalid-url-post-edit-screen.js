/* exported ampInvalidUrlPostEditScreen */

var ampInvalidUrlPostEditScreen = ( function() { // eslint-disable-line no-unused-vars
	var component;

	component = {
		data: {
			l10n: {
				unsaved_changes: '',
				showing_number_errors: '',
				page_heading: '',
				show_all: ''
			}
		}
	};

	/**
	 * The id for the 'Showing x of y errors' notice.
	 *
	 * @var {string}
	 */
	component.idNumberErrors = 'number-errors';

	/**
	 * The id for the 'Show all' button.
	 *
	 * @var {string}
	 */
	component.showAllId = 'show-all-errors';

	/**
	 * Boot.
	 *
	 * @param {Object} data Data.
	 * @param {Object} data.l10n Translations.
	 */
	component.boot = function boot( data ) {
		Object.assign( component.data, data );
		component.handleShowAll();
		component.handleFiltering();
		component.handleSearching();
		component.handleStatusChange();
		component.handleBulkActionCheckboxes();
		component.changeHeading();
		component.watchForUnsavedChanges();
	};

	/**
	 * Watch for unsaved changes.
	 *
	 * Add an beforeunload warning when attempting to leave the page when there are unsaved changes,
	 * unless the user is pressing the trash link or update button.
	 */
	component.watchForUnsavedChanges = function watchForUnsavedChanges() {
		var onChange = function( event ) {
			if ( event.target.matches( 'select' ) ) {
				document.getElementById( 'amp_validation_errors' ).removeEventListener( 'change', onChange );

				window.addEventListener( 'beforeunload', component.onBeforeUnload );

				// Remove prompt when clicking trash or update.
				document.querySelector( '#major-publishing-actions' ).addEventListener( 'click', function() {
					window.removeEventListener( 'beforeunload', component.onBeforeUnload );
				} );
			}
		};
		document.getElementById( 'amp_validation_errors' ).addEventListener( 'change', onChange );
	};

	/**
	 * Show message at beforeunload.
	 *
	 * @param {Event} event - The beforeunload event.
	 * @return {string} Message.
	 */
	component.onBeforeUnload = function onBeforeUnload( event ) {
		event.preventDefault();
		event.returnValue = component.data.l10n.unsaved_changes;
		return component.data.l10n.unsaved_changes;
	};

	/**
	 * Updates the <tr> with 'Showing x of y validation errors' at the top of the list table with the current count.
	 * If this does not exist yet, it creates the element.
	 *
	 * @param {number} numberErrorsDisplaying - The number of errors displaying.
	 * @param {number} totalErrors - The total number of errors, displaying or not.
	 */
	component.updateShowingErrorsRow = function updateShowingErrorsRow( numberErrorsDisplaying, totalErrors ) {
		var thead, tr, th,
			theadQuery = document.getElementsByTagName( 'thead' );

		// Only create the <tr> if it does not exist yet.
		if ( theadQuery[ 0 ] && ! document.getElementById( component.idNumberErrors ) ) {
			thead = theadQuery[ 0 ];
			tr = document.createElement( 'tr' );
			th = document.createElement( 'th' );
			th.setAttribute( 'id', component.idNumberErrors );
			th.setAttribute( 'colspan', '6' );
			tr.appendChild( th );
			thead.appendChild( tr );
		}

		if ( numberErrorsDisplaying === totalErrors ) {
			// If all of the errors are displaying, hide this message and the 'Show all' button.
			document.getElementById( component.idNumberErrors ).classList.add( 'hidden' );
			document.getElementById( component.showAllId ).classList.add( 'hidden' );
		} else if ( null !== numberErrorsDisplaying ) {
			// Update the number of errors displaying and create a 'Show all' button if it does not exist yet.
			document.getElementById( component.idNumberErrors ).innerText = component.data.l10n.showing_number_errors.replace( '%', numberErrorsDisplaying );
			document.getElementById( component.idNumberErrors ).classList.remove( 'hidden' );
			component.conditionallyCreateShowAllButton();
			document.getElementById( component.showAllId ).classList.remove( 'hidden' );
		}
	};

	/**
	 * Conditionally creates and appends a 'Show all' button.
	 */
	component.conditionallyCreateShowAllButton = function conditionallyCreateShowAllButton() {
		var buttonContainer = document.getElementById( 'url-post-filter' ),
			showAllButton = document.getElementById( component.showAllId );

		// There is no 'Show all' <button> yet, but there is a container element for it, create the <button>
		if ( ! showAllButton && buttonContainer ) {
			showAllButton = document.createElement( 'button' );
			showAllButton.id = component.showAllId;
			showAllButton.classList.add( 'button' );
			showAllButton.innerText = component.data.l10n.show_all;
			buttonContainer.appendChild( showAllButton );
		}
	};

	/**
	 * On clicking the 'Show all' <button>, this displays all of the validation errors.
	 * Then, it hides this 'Show all' <button> and the notice for the number of errors showing.
	 */
	component.handleShowAll = function handleShowAll() {
		var onClick = function( event ) {
			if ( ! event.target.matches( '#' + component.showAllId ) ) {
				return;
			}
			event.preventDefault();

			// Iterate through all of the errors, and remove the 'hidden' class.
			document.querySelectorAll( '[data-error-type]' ).forEach( function( element ) {
				element.parentElement.parentElement.classList.remove( 'hidden' );
			} );

			// Hide this 'Show all' button.
			event.target.classList.add( 'hidden' );

			// Hide the 'Showing x of y errors' notice.
			document.getElementById( component.idNumberErrors ).classList.add( 'hidden' );

			// Change the value of the error type <select> element to 'All Error Types'.
			document.getElementById( 'amp_validation_error_type' ).value = '-1';
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
	component.handleFiltering = function handleFiltering() {
		var onChange = function( event ) {
			var errorTypeQuery,
				numberErrorsDisplaying = 0;

			if ( ! event.target.matches( 'select' ) ) {
				return;
			}

			event.preventDefault();

			errorTypeQuery = document.querySelectorAll( '[data-error-type]' );

			/*
			 * Iterate through all of the <tr> elements in the list table.
			 * If the error type does not match the value (selected error type), hide them.
			 */
			errorTypeQuery.forEach( function( element ) {
				var errorType = element.getAttribute( 'data-error-type' );

				// If the value is '-1', 'All Error Types' was selected, and this should display all errors.
				if ( event.target.value === errorType || ! event.target.value || '-1' === event.target.value ) {
					element.parentElement.parentElement.classList.remove( 'hidden' );
					numberErrorsDisplaying++;
				} else {
					element.parentElement.parentElement.classList.add( 'hidden' );
				}
			} );

			component.updateShowingErrorsRow( numberErrorsDisplaying, errorTypeQuery.length );
		};

		document.getElementById( 'amp_validation_error_type' ).addEventListener( 'change', onChange );
	};

	/**
	 * Handles searching for errors via the <input> and the 'Search Errors' <button>.
	 */
	component.handleSearching = function handleSearching() {
		var onClick = function( event ) {
			var searchQuery, detailsQuery,
				numberErrorsDisplaying = 0;

			event.preventDefault();
			if ( ! event.target.matches( 'input' ) ) {
				return;
			}

			searchQuery = document.getElementById( 'invalid-url-search-search-input' ).value;
			detailsQuery = document.querySelectorAll( 'tbody .column-details' );

			/*
			 * Iterate through the 'Details' column of each row.
			 * If the search query is not present, hide the row.
			 */
			detailsQuery.forEach( function( element ) {
				var isSearchQueryPresent = false;

				element.querySelectorAll( '.detailed' ).forEach( function( detailed ) {
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

			component.updateShowingErrorsRow( numberErrorsDisplaying, detailsQuery.length );
		};

		document.getElementById( 'search-submit' ).addEventListener( 'click', onClick );
	};

	/**
	 * Handles a change in the error status, like from 'New' to 'Accepted'.
	 *
	 * Gets the data-status-icon value from the newly-selected <option>.
	 * And sets this as the src of the status icon <img>.
	 */
	component.handleStatusChange = function handleStatusChange() {
		var onChange = function( event ) {
			var newOption, iconSrc;
			if ( ! event.target.matches( 'select' ) ) {
				return;
			}

			newOption = event.target.options[ event.target.selectedIndex ];
			if ( newOption ) {
				iconSrc = newOption.getAttribute( 'data-status-icon' );
				event.target.parentNode.querySelector( 'img' ).setAttribute( 'src', iconSrc );
			}
		};

		document.querySelectorAll( '.amp-validation-error-status' ).forEach( function( element ) {
			element.addEventListener( 'change', onChange );
		} );
	};

	/**
	 * On checking a bulk action checkbox, this ensures that the 'Accept' and 'Reject' buttons are present.
	 *
	 * They're hidden until one of these boxes is checked.
	 * Also, on unchecking the last checked box, this hides these buttons.
	 */
	component.handleBulkActionCheckboxes = function handleStatusChange() {
		var onChange = function( event ) {
			var areThereCheckedBoxes,
				acceptButton = document.querySelector( '[value=amp_validation_error_accept' ),
				rejectButton = document.querySelector( '[value=amp_validation_error_reject' );

			if ( ! event.target.matches( '[type=checkbox]' ) ) {
				return;
			}

			if ( event.target.checked ) {
				// This checkbox was checked, so ensure the buttons display.
				rejectButton.classList.remove( 'hidden' );
				rejectButton.classList.remove( 'hidden' );
			} else {
				/*
				 * This checkbox was unchecked.
				 * So find if there are any other checkboxes that are checked.
				 * If not, hide the 'Accept' and 'Reject' buttons.
				 */
				areThereCheckedBoxes = false;
				document.querySelectorAll( '.check-column [type=checkbox]' ).forEach( function( element ) {
					if ( element.checked ) {
						areThereCheckedBoxes = true;
					}
				} );
				if ( ! areThereCheckedBoxes ) {
					acceptButton.classList.add( 'hidden' );
					rejectButton.classList.add( 'hidden' );
				}
			}
		};

		document.querySelectorAll( '.check-column [type=checkbox]' ).forEach( function( element ) {
			element.addEventListener( 'change', onChange );
		} );
	};

	/**
	 * Changes the page heading, as this doesn't look to be possible with a PHP filter.
	 */
	component.changeHeading = function changeHeading() {
		var headingQuery = document.getElementsByClassName( 'wp-heading-inline' );
		if ( headingQuery[ 0 ] && component.data.l10n.page_heading ) {
			headingQuery[ 0 ].innerText = component.data.l10n.page_heading;
		}
	};

	return component;
}() );
