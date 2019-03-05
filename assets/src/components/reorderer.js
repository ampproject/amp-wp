/**
 * WordPress dependencies
 */
import { BlockEdit } from '@wordpress/editor';
import { createBlock } from '@wordpress/blocks';
import { Disabled } from '@wordpress/components';

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

const Reorderer = ( { pages } ) => {
	return (
		<div className="amp-story-reorderer">
			{ pages.map( ( page, index ) => {
				return (
					<div
						key={ index }
						className="amp-story-page-preview"
					>
						<BlockPreview { ...page } />
					</div>
				);
			} ) }
		</div>
	);
};

export default Reorderer;
