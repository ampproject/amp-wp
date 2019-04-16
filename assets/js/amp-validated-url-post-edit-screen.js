/* exported ampValidatedUrlPostEditScreen */

const ampValidatedUrlPostEditScreen = ( function() { // eslint-disable-line no-unused-vars
	let component = {
		data: {
			l10n: {
				unsaved_changes: '',
				showing_number_errors: '',
				page_heading: '',
				show_all: '',
				amp_enabled: false
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
		component.handleBulkActions();
		component.changeHeading();
		component.watchForUnsavedChanges();
		component.showAMPIconIfEnabled();
	};

	/**
	 * Add prompt when leaving page due to unsaved changes.
	 */
	component.addBeforeUnloadPrompt = function addBeforeUnloadPrompt() {
		if ( component.beforeUnloadPromptAdded ) {
			return;
		}
		window.addEventListener( 'beforeunload', component.onBeforeUnload );

		// Remove prompt when clicking trash or update.
		document.querySelector( '#major-publishing-actions' ).addEventListener( 'click', function() {
			window.removeEventListener( 'beforeunload', component.onBeforeUnload );
		} );

		component.beforeUnloadPromptAdded = true;
	};

	/**
	 * Watch for unsaved changes.
	 *
	 * Add an beforeunload warning when attempting to leave the page when there are unsaved changes,
	 * unless the user is pressing the trash link or update button.
	 */
	component.watchForUnsavedChanges = function watchForUnsavedChanges() {
		const onChange = function( event ) {
			if ( event.target.matches( 'select' ) ) {
				document.getElementById( 'post' ).removeEventListener( 'change', onChange );
				component.addBeforeUnloadPrompt();
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
		const showAllButton = document.getElementById( component.showAllId );
		let thead, th,
			tr = document.getElementById( component.idNumberErrors );
		const theadQuery = document.getElementsByTagName( 'thead' );

		// Only create the <tr> if it does not exist yet.
		if ( theadQuery[ 0 ] && ! tr ) {
			thead = theadQuery[ 0 ];
			tr = document.createElement( 'tr' );
			th = document.createElement( 'th' );
			th.setAttribute( 'id', component.idNumberErrors );
			th.setAttribute( 'colspan', '6' );
			tr.appendChild( th );
			thead.appendChild( tr );
		}

		// If all of the errors are displaying, hide the 'Show all' button and the count notice.
		if ( showAllButton && numberErrorsDisplaying === totalErrors ) {
			showAllButton.classList.add( 'hidden' );
			tr.classList.add( 'hidden' );
		} else if ( null !== numberErrorsDisplaying ) {
			// Update the number of errors displaying and create a 'Show all' button if it does not exist yet.
			document.getElementById( component.idNumberErrors ).innerText = component.data.l10n.showing_number_errors.replace( '%1$s', numberErrorsDisplaying );
			document.getElementById( component.idNumberErrors ).classList.remove( 'hidden' );
			component.conditionallyCreateShowAllButton();
			if ( document.getElementById( component.showAllId ) ) {
				document.getElementById( component.showAllId ).classList.remove( 'hidden' );
			}
		}
	};

	/**
	 * Conditionally creates and appends a 'Show all' button.
	 */
	component.conditionallyCreateShowAllButton = function conditionallyCreateShowAllButton() {
		const buttonContainer = document.getElementById( 'url-post-filter' );
		let showAllButton = document.getElementById( component.showAllId );

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
		const onClick = function( event ) {
			const validationErrors = document.querySelectorAll( '[data-error-type]' );
			if ( ! event.target.matches( '#' + component.showAllId ) ) {
				return;
			}
			event.preventDefault();

			// Iterate through all of the errors, and remove the 'hidden' class.
			validationErrors.forEach( function( element ) {
				element.parentElement.parentElement.classList.remove( 'hidden' );
			} );

			/*
			 * Update the notice to indicate that all of the errors are displaying.
			 * Like 'Showing 5 of 5 validation errors'.
 			 */
			component.updateShowingErrorsRow( validationErrors.length, validationErrors.length );

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
	component.handleFiltering = function handleFiltering() {
		const onChange = function( event ) {
			const showAllButton = document.getElementById( component.showAllId );
			if ( ! event.target.matches( 'select' ) ) {
				return;
			}

			event.preventDefault();

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
			errorTypeQuery.forEach( function( element ) {
				const errorType = element.getAttribute( 'data-error-type' );

				// If 'All Error Types' was selected, this should display all errors.
				if ( isAllErrorTypesSelected || ! event.target.value || event.target.value === errorType ) {
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
		const onClick = function( event ) {
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
			detailsQuery.forEach( function( element ) {
				let isSearchQueryPresent = false;

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
	 * Update icon for select element.
	 *
	 * @param {HTMLSelectElement} select Select element.
	 */
	component.updateSelectIcon = function updateSelectIcon( select ) {
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
	component.handleStatusChange = function handleStatusChange() {
		const setRowStatusClass = function( { row, select } ) {
			const acceptedValue = 3;
			const rejectedValue = 2;
			const status = parseInt( select.options[ select.selectedIndex ].value );

			row.classList.toggle( 'new', isNaN( status ) );
			row.classList.toggle( 'accepted', acceptedValue === status );
			row.classList.toggle( 'rejected', rejectedValue === status );
		};

		const onChange = function( { event, row, select } ) {
			if ( event.target.matches( 'select' ) ) {
				component.updateSelectIcon( event.target );
				setRowStatusClass( { row, select } );
			}
		};

		document.querySelectorAll( 'tr[id^="tag-"]' ).forEach( function( row ) {
			const select = row.querySelector( '.amp-validation-error-status' );

			if ( select ) {
				setRowStatusClass( { row, select } );
				select.addEventListener( 'change', function( event ) {
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
	component.handleBulkActions = function handleBulkActions() {
		const acceptButton = document.querySelector( 'button.action.accept' );
		const rejectButton = document.querySelector( 'button.action.reject' );
		const acceptAndRejectContainer = document.getElementById( 'accept-reject-buttons' );

		const onChange = function( event ) {
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
				document.querySelectorAll( '.check-column [type=checkbox]' ).forEach( function( element ) {
					if ( element.checked ) {
						areThereCheckedBoxes = true;
					}
				} );
				if ( ! areThereCheckedBoxes ) {
					acceptAndRejectContainer.classList.add( 'hidden' );
				}
			}
		};

		document.querySelectorAll( '.check-column [type=checkbox]' ).forEach( function( element ) {
			element.addEventListener( 'change', onChange );
		} );

		// Handle click on accept button.
		acceptButton.addEventListener( 'click', function() {
			Array.prototype.forEach.call( document.querySelectorAll( 'select.amp-validation-error-status' ), function( select ) {
				if ( select.closest( 'tr' ).querySelector( '.check-column input[type=checkbox]' ).checked ) {
					select.value = '3';
					component.updateSelectIcon( select );
					component.addBeforeUnloadPrompt();
				}
			} );
		} );

		// Handle click on reject button.
		rejectButton.addEventListener( 'click', function() {
			Array.prototype.forEach.call( document.querySelectorAll( 'select.amp-validation-error-status' ), function( select ) {
				if ( select.closest( 'tr' ).querySelector( '.check-column input[type=checkbox]' ).checked ) {
					select.value = '2';
					component.updateSelectIcon( select );
					component.addBeforeUnloadPrompt();
				}
			} );
		} );
	};

	/**
	 * Changes the page heading and document title, as this doesn't look to be possible with a PHP filter.
	 */
	component.changeHeading = function changeHeading() {
		const headingQuery = document.getElementsByClassName( 'wp-heading-inline' );
		if ( headingQuery[ 0 ] && component.data.l10n.page_heading ) {
			headingQuery[ 0 ].innerText = component.data.l10n.page_heading;
			document.title = component.data.l10n.page_heading + document.title;
		}
	};

	/**
	 * Adds the AMP icon to the page heading if AMP is enabled on this URL.
	 */
	component.showAMPIconIfEnabled = function() {
		const heading = document.querySelector( 'h1.wp-heading-inline' );
		if ( heading && true === component.data.l10n.amp_enabled ) {
			const ampIcon = document.createElement( 'span' );
			ampIcon.classList.add( 'status-text', 'sanitized' );
			heading.appendChild( ampIcon );
		}
	};

	return component;
}() );
