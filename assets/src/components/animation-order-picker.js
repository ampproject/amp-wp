/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Dashicon, BaseControl, Button, Dropdown, NavigableMenu } from '@wordpress/components';
import { BlockIcon } from '@wordpress/editor';
import { Fragment } from '@wordpress/element';

function ButtonContent( { option, displayIcon = true } ) {
	const { label: name, block, blockType } = option;

	if ( ! block ) {
		return name;
	}

	let label = block.clientId;

	// Todo: Cover more special cases if needed.
	switch ( block.name ) {
		case 'core/image':
			if ( block.attributes.url ) {
				label = block.attributes.url.slice( block.attributes.url.lastIndexOf( '/' ) );
			}

			break;
		case 'amp/amp-story-text':
			const content = block.originalContent ? block.originalContent.replace( /<[^<>]+>/g, ' ' ) : '';

			label = content.slice( 0, 20 );
			break;
		default:
			label = block.clientId;
	}

	return (
		<Fragment>
			{ label }
			{ displayIcon && <BlockIcon icon={ blockType.icon } /> }
		</Fragment>
	);
}

/**
 * Font Family Picker component.
 *
 * @return {?Object} The rendered component or null if there are no options.
 */
function AnimationOrderPicker( {
	value = '',
	options,
	onChange,
	label,
} ) {
	const defaultOption = {
		value: '',
		label: __( 'Immediately', 'amp' ),
	};

	options.unshift( defaultOption );

	const currentOption = options.find( ( option ) => option.value === value ) || defaultOption;
	/* translators: %s: block name */
	const ariaLabel = currentOption ? sprintf(	__( 'Begin after: %s', 'amp' ), currentOption.label ) : __( 'Begin immediately', 'amp' );

	return (
		<BaseControl label={ label || __( 'Begin after', 'amp' ) }>
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
							aria-label={ ariaLabel }
						>
							<ButtonContent option={ currentOption } displayIcon={ false } />
						</Button>
					) }
					renderContent={ () => (
						<NavigableMenu>
							{ options.map( ( option ) => {
								const isSelected = ( option.value === value );

								return (
									<Button
										key={ option.value }
										onClick={ () => onChange( option.value === '' ? undefined : option.block.clientId ) }
										role="menuitemradio"
										aria-checked={ isSelected }
									>
										{ isSelected && <Dashicon icon="saved" /> }
										<span className="components-preview-picker__dropdown-animation-order">
											<ButtonContent option={ option } />
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

export default AnimationOrderPicker;
