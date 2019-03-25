/**
 * External dependencies
 */
import { noop } from 'lodash';

/**
 * WordPress dependencies
 */
import { createBlock } from '@wordpress/blocks';
import { Disabled } from '@wordpress/components';
import { BlockEdit } from '@wordpress/block-editor';

/**
 * Block Preview Component: It renders a preview given a block name and attributes.
 *
 * @param {Object} props Component props.
 *
 * @return {WPElement} Rendered element.
 */
function BlockPreview( props ) {
	return (
		<div className="editor-block-preview block-editor-block-preview">
			<BlockPreviewContent { ...props } />
		</div>
	);
}

export function BlockPreviewContent( { name, attributes } ) {
	const blocks = wp.blocks.parse( attributes.content );
	if ( ! blocks.length ) {
		return null;
	}
	const block = createBlock( 'core/template', {}, blocks );
	return (
		<Disabled className="editor-block-preview__content block-editor-block-preview__content editor-styles-wrapper" aria-hidden>
			<BlockEdit
				name={ name }
				focus={ false }
				attributes={ block.attributes }
				setAttributes={ noop }
			/>
		</Disabled>
	);
}

export default BlockPreview;
