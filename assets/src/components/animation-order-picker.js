/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { PreviewPicker, BlockPreviewLabel } from './';

/**
 * Animation order picker component.
 *
 * @return {?Object} The rendered component or null if there are no options.
 */
function AnimationOrderPicker( {
	value = '',
	options,
	onChange,
} ) {
	const defaultOption = {
		value: '',
		label: __( 'Immediately', 'amp' ),
	};

	return (
		<PreviewPicker
			value={ value }
			options={ options }
			defaultOption={ defaultOption }
			onChange={ ( { value: selectedValue, block } ) => onChange( selectedValue === '' ? undefined : block.clientId ) }
			label={ __( 'Begin after', 'amp' ) }
			ariaLabel={ ( { value: currentValue, blockType } ) => ! currentValue ? __( 'Begin immediately', 'amp' ) : sprintf( __( 'Begin after: %s', 'amp' ), blockType.title ) }
			renderToggle={ ( currentOption ) => (
				<BlockPreviewLabel
					{ ...currentOption }
					displayIcon={ false }
					alignIcon="right"
				/>
			) }
			renderOption={ ( option ) => {
				return (
					<span className="components-preview-picker__dropdown-label">
						<BlockPreviewLabel
							{ ...option }
							alignIcon="right"
						/>
					</span>
				);
			} }
		/>
	);
}

export default AnimationOrderPicker;
