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
import { withInstanceId } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { maybeEnqueueFontStyle } from '../../helpers';
import 'accessible-autocomplete/src/autocomplete.css';
import './edit.css';

// Override method on class as not required.
Autocomplete.prototype.handleInputBlur = () => {};
/**
 * Font Family Picker component.
 *
 * @return {?Object} The rendered component or null if there are no options.
 */
function FontFamilyPicker( {
	fonts = [],
	onChange = () => {},
	value = '',
	instanceId,
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
			id={ `amp-stories-font-family-picker-${ instanceId }` }
			help={ __( 'Type to search for fonts', 'amp' ) }
		>
			<Autocomplete
				id={ `amp-stories-font-family-picker-${ instanceId }` }
				source={ suggest }
				templates={
					{
						suggestion: suggestionTemplate,
						inputValue: inputValueTemplate,
					}
				}
				minLength={ 2 }
				onConfirm={ onChange }
				showAllValues={ false }
				confirmOnBlur={ false }
				defaultValue={ value }
				dropdownArrow={ () => '' }
				preserveNullOptions={ true }
				placeholder={ __( 'None', 'amp' ) }
				displayMenu="inline"
				tNoResults={ () =>
					__( 'No font found', 'amp' )
				}
				tStatusQueryTooShort={ ( minQueryLength ) =>
					// translators: %d: the number characters required to initiate a font search.
					sprintf( __( 'Type in %d or more characters for results', 'amp' ), minQueryLength )
				}
				tStatusSelectedOption={ ( selectedOption, length ) =>
					// translators: 1: the index of the selected result. 2: The total number of results.
					sprintf( __( '%1$s (1 of %2$s) is selected', 'amp' ), selectedOption, length )
				}
				tStatusResults={ ( length, contentSelectedOption ) => {
					return (
						// translators: %d: The total number of results.
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
	instanceId: PropTypes.number.isRequired,
};

export default withInstanceId( FontFamilyPicker );
