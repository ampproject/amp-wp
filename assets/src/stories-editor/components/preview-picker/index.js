/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { BaseControl, Button, Dashicon, Dropdown, NavigableMenu } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './edit.css';

/**
 * Preview picker component.
 *
 * @return {?Object} The rendered component or null if there are no options.
 */
function PreviewPicker( {
	value,
	options,
	defaultOption,
	onChange,
	label,
	id,
	renderToggle,
	renderOption,
	ariaLabel,
} ) {
	const currentOption = options.find( ( option ) => value && option.value === value ) || defaultOption;

	return (
		<BaseControl label={ label } id={ id }>
			<div className="components-preview-picker__buttons">
				<Dropdown
					className="components-preview-picker__dropdown"
					contentClassName="components-preview-picker__dropdown-content"
					position="bottom"
					renderToggle={ ( { isOpen, onToggle } ) => (
						<Button
							className="components-preview-picker__selector"
							isLarge
							onClick={ onToggle }
							aria-expanded={ isOpen }
							aria-label={ ariaLabel( currentOption ) }
						>
							{ renderToggle( currentOption ) }
						</Button>
					) }
					renderContent={ () => (
						<NavigableMenu>
							{ [ defaultOption, ...options ].map( ( option ) => {
								const isSelected = option.value === currentOption.value;

								return (
									<Button
										key={ option.value }
										onClick={ () => onChange( option ) }
										role="menuitemradio"
										aria-checked={ isSelected }
									>
										{ isSelected && <Dashicon icon="saved" /> }
										{ renderOption( option ) }
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

PreviewPicker.propTypes = {
	value: PropTypes.string,
	options: PropTypes.arrayOf( PropTypes.shape( {
		value: PropTypes.string.isRequired,
		label: PropTypes.string.isRequired,
	} ) ),
	defaultOption: PropTypes.object.isRequired,
	onChange: PropTypes.func.isRequired,
	label: PropTypes.string.isRequired,
	id: PropTypes.string.isRequired,
	renderToggle: PropTypes.func.isRequired,
	renderOption: PropTypes.func.isRequired,
	ariaLabel: PropTypes.func.isRequired,
};

export default PreviewPicker;
