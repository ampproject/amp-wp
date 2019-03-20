/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { BlockIcon } from '@wordpress/editor';
import { Fragment } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { PreviewPicker } from './';

function ButtonContent( { option, displayIcon = true } ) {
	const { label: name, block, blockType } = option;

	if ( ! block ) {
		return name;
	}

	let label;

	// Todo: Cover more special cases if needed.
	switch ( block.name ) {
		case 'core/image':
			if ( block.attributes.url ) {
				const content = block.attributes.url.slice( block.attributes.url.lastIndexOf( '/' ) ).slice( 0, 30 );

				if ( content.length > 0 ) {
					label = content;
				}
			}

			break;
		case 'amp/amp-story-text':
			const content = block.attributes.content.length > 0 ? block.attributes.content.replace( /<[^<>]+>/g, ' ' ).slice( 0, 30 ) : '';

			label = content.length > 0 ? content : blockType.title;
			break;
		default:
			label = blockType.title;
	}

	return (
		<Fragment>
			{ label.length > 20 ? `${ label.substr( 0, 20 ) }…` : label }
			{ displayIcon && <BlockIcon icon={ blockType.icon } /> }
		</Fragment>
	);
}

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
				<ButtonContent option={ currentOption } displayIcon={ false } />
			) }
			renderOption={ ( option ) => {
				return (
					<span className="components-preview-picker__dropdown-label">
						<ButtonContent option={ option } />
					</span>
				);
			} }
		/>
	);
}

export default AnimationOrderPicker;
