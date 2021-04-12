
/**
 * Set the initial 'new' (aka 'unseen') class names on the rows based on the presence of a hidden input value.
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
	} );
}
