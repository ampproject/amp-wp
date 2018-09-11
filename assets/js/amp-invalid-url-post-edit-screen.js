/* exported ampInvalidUrlPostEditScreen */

var ampInvalidUrlPostEditScreen = ( function() { // eslint-disable-line no-unused-vars
	var component;

	component = {
		data: {
			l10n: {
				unsaved_changes: '',
				showing_number_errors: ''
			}
		}
	};

	/**
	 * Boot.
	 *
	 * @param {Object} data Data.
	 * @param {Object} data.l10n Translations.
	 */
	component.boot = function boot( data ) {
		Object.assign( component.data, data );
		component.addShowingErrorsRow();
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
	 * Add the <tr> with 'Showing 4 of x validation errors' at the top of the list table.
	 */
	component.addShowingErrorsRow = function addShowingErrorsRow() {
		var thead, tr, th,
			theadQuery = document.getElementsByTagName( 'thead' );

		/*
		 * If there are no validation errors, like if someone filters for 'JS Errors',
		 * there won't be translated text in showing_number_errors.
		 * In that case, there's no need to output this message.
		 */
		if ( ! theadQuery[ 0 ] || ! component.data.l10n.showing_number_errors ) {
			return;
		}

		thead = theadQuery[ 0 ];
		tr = document.createElement( 'tr' );
		th = document.createElement( 'th' );

		/* eslint-disable dot-notation */
		th.style[ 'text-align' ] = 'center';
		th.style[ 'background-color' ] = '#d3d3d3b8';
		th.style[ 'color' ] = '#1e8cbecc';
		/* eslint-enable dot-notation */

		th.innerText = component.data.l10n.showing_number_errors;
		th.setAttribute( 'colspan', '6' );

		tr.appendChild( th );
		thead.appendChild( tr );
	};

	return component;
}() );
