/**
 * Set the initial class names on the errors rows.
 *
 * This is needed because \WP_Terms_List_Table::single_row() does not allow for additional
 * attributes to be added to the <tr> element.
 */
export default function() {
	document.querySelectorAll( 'tr[id]' ).forEach( ( row ) => {
		setStatusNew( row );
		setStatusKept( row );
	} );
}

/**
 * Set the initial 'new' (aka 'unseen') class names on the rows based on the presence of a hidden input value.
 *
 * @param {Element} row HTML row element.
 */
function setStatusNew( row ) {
	// Look inside the `.column-status` only so that the AMP Validated URLs table is not affected.
	const input = row.querySelector( '.column-status .amp-validation-error-new' );

	if ( ! input ) {
		return;
	}

	row.classList.toggle( 'new', Boolean( parseInt( input.value ) ) );
}

/**
 * Set the 'kept' class names based on the select input field state.
 *
 * @param {Element} row HTML row element.
 */
function setStatusKept( row ) {
	const input = row.querySelector( '.amp-validation-error-status' );

	if ( ! input ) {
		return;
	}

	const { tagName, value } = input;
	const hasClass = tagName === 'SELECT'
		? value === '2' // See AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_REJECTED_STATUS.
		: value === '0'; // '0' -> kept; '1' -> removed

	row.classList.toggle( 'kept', hasClass );
}
