/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import Autocomplete from 'accessible-autocomplete/react';

/**
 * WordPress dependencies
 */
import { __, _n, sprintf } from '@wordpress/i18n';
import { BaseControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { maybeEnqueueFontStyle } from '../../helpers';
import 'accessible-autocomplete/src/autocomplete.css';
import './edit.css';

/**
 * Font Family Picker component.
 *
 * @return {?Object} The rendered component or null if there are no options.
 */
function FontFamilyPicker( {
	fonts = [],
	onChange = () => {},
	value = '',
} ) {
	const results = fonts;
	const suggest = ( query, syncResults ) => {
		const searchResults = query ? results.filter( function( result ) {
			return result.name.toLowerCase().indexOf( query.toLowerCase() ) !== -1;
		} ) :
			[];
		syncResults( searchResults );
	};

	const suggestionTemplate = ( result ) => {
		maybeEnqueueFontStyle( result.name );
		const fallbacks = ( result.fallbacks ) ? ', ' + result.fallbacks.join( ', ' ) : '';
		return result && `<span style='font-family: ${ result.name }${ fallbacks }'>${ result.name }</span>`;
	};

	const inputValueTemplate = ( result ) => {
		return result && result.name;
	};

	return (
		<BaseControl
			label={ __( 'Font Family', 'amp' ) }
			id="amp-stories-font-family-picker"
		>
			<Autocomplete
				id="amp-stories-font-family-picker"
				source={ suggest }
				templates={
					{ suggestion: suggestionTemplate, inputValue: inputValueTemplate }
				}
				minLength={ 2 }
				onConfirm={ onChange }
				showAllValues={ false }
				confirmOnBlur={ false }
				defaultValue={ value }
				dropdownArrow={ () => '' }
				preserveNullOptions={ true }
				placeholder={ __( 'None', 'amp' ) }
				showNoOptionsFound={ false }
				displayMenu="overlay"
				tStatusQueryTooShort={ ( minQueryLength ) =>
					// translators: %d: the number characters required to initiate an author search.
					sprintf( __( 'Type in %d or more characters for results', 'amp' ), minQueryLength )
				}
				// translators: 1: the index of thre selected result. 2: The total number of results.
				tStatusSelectedOption={ ( selectedOption, length ) => sprintf( __( '%1$s (1 of %2$s) is selected', 'amp' ), selectedOption, length ) }
				tStatusResults={ ( length, contentSelectedOption ) => {
					return (
						_n( '%d font is available.', '%d fonts are available.', length, 'amp' ) +
						' ' + contentSelectedOption
					);
				} }
			/>
		</BaseControl>
	);
}

FontFamilyPicker.propTypes = {
	value: PropTypes.string,
	fonts: PropTypes.arrayOf( PropTypes.shape( {
		value: PropTypes.string,
		label: PropTypes.string,
	} ) ),
	onChange: PropTypes.func,
};

export default FontFamilyPicker;
