/* exported ampInvalidUrlPostEditScreen */

var ampInvalidUrlPostEditScreen = ( function() { // eslint-disable-line no-unused-vars
	var component;

	component = {
		data: {
			l10n: {
				unsaved_changes: ''
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
		component.addShowingErrrsRow();
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
	component.addShowingErrrsRow = function addShowingErrrsRow() {
		var thead, tr, th,
			theadQuery = document.getElementsByTagName( 'thead' );

		if ( undefined === theadQuery[ 0 ] ) {
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
		th.innerText = 'Showing 4 of 12 validation errors';
		th.setAttribute( 'colspan', '6' );

		tr.appendChild( th );
		thead.appendChild( tr );
	};

	return component;
}() );
