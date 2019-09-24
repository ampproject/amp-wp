/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, _n, sprintf } from '@wordpress/i18n';
import { BaseControl } from '@wordpress/components';
import { withInstanceId } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { Autocomplete } from '../';
import { maybeEnqueueFontStyle } from '../../helpers';

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
	const suggest = ( query, populateResults ) => {
		const searchResults = query ?
			results.filter( ( result ) => result.name.toLowerCase().indexOf( query.toLowerCase() ) !== -1 ) :
			[];
		populateResults( searchResults );
	};

	const suggestionTemplate = ( font ) => {
		maybeEnqueueFontStyle( font.name );
		const fallbacks = ( font.fallbacks ) ? ', ' + font.fallbacks.join( ', ' ) : '';
		return font && `<span style='font-family: ${ font.name }${ fallbacks }'>${ font.name }</span>`;
	};

	const inputValueTemplate = ( result ) => {
		return result && result.name;
	};

	const id = `amp-stories-font-family-picker-${ instanceId }`;

	return (
		<BaseControl
			label={ __( 'Font Family', 'amp' ) }
			id={ id }
			help={ __( 'Type to search for fonts', 'amp' ) }
		>
			<Autocomplete
				id={ id }
				name={ id }
				source={ suggest }
				templates={
					{
						suggestion: suggestionTemplate,
						inputValue: inputValueTemplate,
					}
				}
				ariaLabelBy={ `${id}__help` }
				minLength={ 2 }
				onConfirm={ onChange }
				showAllValues={ false }
				confirmOnBlur={ false }
				defaultValue={ value || '' }
				dropdownArrow={ () => '' }
				preserveNullOptions={ true }
				placeholder={ __( 'None', 'amp' ) }
				displayMenu="overlay"
				tNoResults={ () =>
					__( 'No font found', 'amp' )
				}
				tStatusQueryTooShort={ ( minQueryLength ) =>
					// translators: %d: the number characters required to initiate a font search.
					sprintf( __( 'Type in %s or more characters for results', 'amp' ), minQueryLength )
				}
				tStatusSelectedOption={ ( selectedOption, length ) =>
					// translators: 1: the index of the selected result. 2: The total number of results.
					sprintf( __( '%s (1 of %s) is selected', 'amp' ), selectedOption, length )
				}
				tStatusResults={ ( length, contentSelectedOption ) => {
					return (
						sprintf(
							// translators: %d: The total number of results.
							_n( '%d font is available. %s', '%d fonts are available. %s', length, 'amp' ),
							length,
							contentSelectedOption
						)
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
