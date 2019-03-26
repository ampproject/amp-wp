/**
 * External dependencies
 */
import { noop } from 'lodash';

/**
 * WordPress dependencies
 */
import { createBlock } from '@wordpress/blocks';
import { Disabled } from '@wordpress/components';
// import { BlockEdit } from '@wordpress/block-editor';

/**
 * Block Preview Component: It renders a preview given a block name and attributes.
 *
 * @param {Object} props Component props.
 *
 * @return {WPElement} Rendered element.
 */
function BlockPreview( props ) {
	return (
		<button onClick={ props.onClick } className="components-button editor-block-preview block-editor-block-preview">
			<BlockPreviewContent { ...props } />
		</button>
	);
}

export function BlockPreviewContent( { name, attributes } ) {
	// @todo Importing this outside of the function causes error for some reason.
	const BlockEdit = wp.blockEditor.BlockEdit;
	const block = createBlock( name, attributes );
	return (
		<Disabled className="editor-block-preview__content block-editor-block-preview__content editor-styles-wrapper" aria-hidden>
			<BlockEdit
				name={ block.name }
				focus={ false }
				attributes={ block.attributes }
				setAttributes={ noop }
			/>
		</Disabled>
	);
}

export default BlockPreview;
