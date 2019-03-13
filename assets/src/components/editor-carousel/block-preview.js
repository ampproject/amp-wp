/**
 * WordPress dependencies
 */
import { createBlock } from '@wordpress/blocks';
import { Disabled } from '@wordpress/components';
import { BlockEdit } from '@wordpress/editor';

/**
 * BlockPreview component that is used within the reordering UI.
 *
 * @return {Object} Block preview.
 */
const BlockPreview = ( { clientId, name, attributes, innerBlocks } ) => {
	const block = createBlock( name, attributes, innerBlocks );
	return (
		<Disabled className="editor-block-preview__content editor-styles-wrapper" aria-hidden>
			<BlockEdit
				name={ name }
				focus={ false }
				clientId={ clientId }
				isLocked={ true }
				insertBlocksAfter={ false }
				attributes={ block.attributes }
				setAttributes={ () => {} }
			/>
		</Disabled>
	);
};

export default BlockPreview;
