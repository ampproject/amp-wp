/**
 * Gets the table rows on a single URL validation screen.
 *
 * @param {Object} options
 * @param {boolean} options.checkedOnly Whether to return only checked rows.
 */
export function getURLValidationTableRows( options = {} ) {
	const rows = [ ...document.querySelectorAll( 'select.amp-validation-error-status' ) ]
		.map( ( select ) => select.closest( 'tr' ) );

	if ( true !== options.checkedOnly ) {
		return rows;
	}

	return rows.filter( ( row ) => row.querySelector( '.check-column input[type=checkbox]' ).checked );
}
