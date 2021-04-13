
/**
 * Set the initial class names on the errors rows.
 *
 * - Set the 'new' (aka 'unseen') class names based on the presence of a hidden input value.
 * - Set the 'kept' class names based on the select input field state.
 *
 * This is needed because \WP_Terms_List_Table::single_row() does not allow for additional
 * attributes to be added to the <tr> element.
 */
export default function() {
	document.querySelectorAll( 'tr[id]' ).forEach( ( row ) => {
		const input = row.querySelector( '.amp-validation-error-new' );
		if ( input ) {
			row.classList.toggle( 'new', Boolean( parseInt( input.value ) ) );
		}

		const statusField = row.querySelector( '.amp-validation-error-status' );

		if ( ! statusField ) {
			return;
		}

		if ( statusField.tagName === 'SELECT' ) {
			row.classList.toggle( 'kept', statusField.value === '2' ); // See AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_REJECTED_STATUS.
		} else {
			row.classList.toggle( 'kept', statusField.value === '0' ); // '0' -> kept; '1' -> removed
		}
	} );
}
