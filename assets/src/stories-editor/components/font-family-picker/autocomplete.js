/**
 * External dependencies
 */
import OriginalAutocomplete from 'accessible-autocomplete/react';

class Autocomplete extends OriginalAutocomplete {
	/**
	 * Overrides default method to prevent an issue with
	 * scrollbars appearing inadvertently.
	 */
	handleInputBlur() {}
}

export default Autocomplete;
