/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Dashicon, BaseControl, Button, Dropdown, NavigableMenu } from '@wordpress/components';

/**
 * Font Family Picker component.
 *
 * @return {?Object} The rendered component or null if there are no options.
 */
function FontFamilyPicker( {
	options = [],
	onChange,
	value = '',
} ) {
	if ( ! options.length ) {
		return null;
	}

	const fontLabel = ( { label, element: Element } ) => Element ? <Element height="20" /> : label;
	const currentFont = options.find( ( font ) => font.value === value );

	return (
		<BaseControl label={ __( 'Font Family', 'amp' ) }>
			<div className="components-font-family-picker__buttons">
				{ ( options.length > 0 ) &&
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
								( currentFont && currentFont.label ) || __( 'None', 'amp' )
							) }
							data-font-family={ currentFont ? currentFont.value : '' }
						>
							{ fontLabel( currentFont ) }
						</Button>
					) }
					renderContent={ () => (
						<NavigableMenu>
							{ options.map( ( { value: slug, label, element } ) => {
								const isSelected = ( value === slug || ( ! value && slug === '' ) );

								return (
									<Button
										key={ slug }
										onClick={ () => onChange( slug === '' ? undefined : slug ) }
										className={ `is-font-${ slug }` }
										role="menuitemradio"
										aria-checked={ isSelected }
									>
										{ isSelected && <Dashicon icon="saved" /> }
										<span className="components-font-family-picker__dropdown-text-size" data-font-family={ slug }>
											{ fontLabel( { label, element } ) }
										</span>
									</Button>
								);
							} ) }
						</NavigableMenu>
					) }
				/>
				}
			</div>
		</BaseControl>
	);
}

export default FontFamilyPicker;
