/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { AMP_STORY_FONT_IMAGES } from '../../constants';
import { PreviewPicker } from '../';

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
	const defaultOption = {
		value: '',
		label: __( 'None', 'amp' ),
	};

	const options = fonts.map( ( font ) => ( {
		value: font.name,
		label: font.name,
	} ) );

	const fontLabel = ( familyName ) => AMP_STORY_FONT_IMAGES[ familyName ] ?
		AMP_STORY_FONT_IMAGES[ familyName ]( { height: 13 } ) :
		familyName;

	return (
		<PreviewPicker
			value={ value }
			options={ options }
			defaultOption={ defaultOption }
			onChange={ ( { value: selectedValue } ) => onChange( '' === selectedValue ? undefined : selectedValue ) }
			label={ __( 'Font Family', 'amp' ) }
			id="amp-stories-font-family-picker"
			ariaLabel={ ( currentOption ) => {
				return sprintf(
					/* translators: %s: font name */
					__( 'Font Family: %s', 'amp' ),
					currentOption.label
				);
			} }
			renderToggle={ ( { label } ) => fontLabel( label ) }
			renderOption={ ( option ) => {
				return (
					<span className="components-preview-picker__dropdown-label" data-font-family={ option.value === '' ? undefined : option.value }>
						{ fontLabel( option.label ) }
					</span>
				);
			} }
		/>
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
