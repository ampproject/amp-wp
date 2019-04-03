/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element';
import { BlockIcon } from '@wordpress/block-editor';

export default ( { label: name, block, blockType, displayIcon = true, alignIcon = 'left', accessibilityText } ) => {
	if ( ! block ) {
		return name;
	}

	let label;
	let content;

	// Todo: Cover more special cases if needed.
	switch ( block.name ) {
		case 'core/image':
			if ( block.attributes.url ) {
				content = block.attributes.url.slice( block.attributes.url.lastIndexOf( '/' ) ).slice( 0, 30 );

				if ( content.length > 0 ) {
					label = content;
				}
			}

			break;
		case 'amp/amp-story-text':
			content = block.attributes.content.length > 0 ? block.attributes.content.replace( /<[^<>]+>/g, ' ' ).slice( 0, 30 ) : '';

			label = content.length > 0 ? content : blockType.title;
			break;
		default:
			label = blockType.title;
	}

	return (
		<Fragment>
			{ displayIcon && 'left' === alignIcon && <BlockIcon icon={ blockType.icon } /> }
			{ label.length > 20 ? `${ label.substr( 0, 20 ) }…` : label }
			{ accessibilityText && <span className="screen-reader-text">{ accessibilityText }</span> }
			{ displayIcon && 'right' === alignIcon && <BlockIcon icon={ blockType.icon } /> }
		</Fragment>
	);
};
