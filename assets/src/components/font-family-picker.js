/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Dashicon, BaseControl, Button, Dropdown, NavigableMenu } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { AMP_STORY_FONT_IMAGES } from '../constants';

/**
 * Font Family Picker component.
 *
 * @return {?Object} The rendered component or null if there are no options.
 */
function FontFamilyPicker( {
	onChange,
	name = '',
} ) {
	if ( ! window.ampStoriesFonts ) {
		return null;
	}

	const noneFont = {
		name: __( 'None', 'amp' ),
		value: '',
	};

	const options = [
		noneFont,
		...window.ampStoriesFonts.map( ( font ) => ( {
			name: font.name,
			value: font.name,
		} ) ),
	];

	const fontLabel = ( { name: familyName } ) => AMP_STORY_FONT_IMAGES[ familyName ] ?
		AMP_STORY_FONT_IMAGES[ familyName ]( { height: 13 } ) :
		familyName;
	const currentFont = options.find( ( font ) => font.name === name ) || noneFont;

	return (
		<BaseControl label={ __( 'Font Family', 'amp' ) }>
			<div className="components-font-family-picker__buttons">
				<Dropdown
					className="components-font-family-picker__dropdown"
					contentClassName="components-font-family-picker__dropdown-content"
					position="bottom"
					renderToggle={ ( { isOpen, onToggle } ) => (
						<Button
							className="components-font-family-picker__selector"
							isLarge
							onClick={ onToggle }
							aria-expanded={ isOpen }
							aria-label={ sprintf(
								/* translators: %s: font name */
								__( 'Font Family: %s', 'amp' ),
								currentFont.name
							) }
							data-font-family={ currentFont.name }
						>
							{ fontLabel( currentFont ) }
						</Button>
					) }
					renderContent={ () => (
						<NavigableMenu>
							{ options.map( ( { name: optionName, value } ) => {
								const isSelected = ( optionName === name || ( ! name && optionName === '' ) );

								return (
									<Button
										key={ optionName }
										onClick={ () => onChange( value === '' ? undefined : value ) }
										role="menuitemradio"
										aria-checked={ isSelected }
									>
										{ isSelected && <Dashicon icon="saved" /> }
										<span className="components-font-family-picker__dropdown-text-size" data-font-family={ value === '' ? undefined : value }>
											{ fontLabel( { name: optionName } ) }
										</span>
									</Button>
								);
							} ) }
						</NavigableMenu>
					) }
				/>
			</div>
		</BaseControl>
	);
}

export default FontFamilyPicker;
