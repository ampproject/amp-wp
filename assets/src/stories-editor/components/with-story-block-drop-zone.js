/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { withBlockName, withHasSelectedInnerBlock, withSelectedBlock, StoryBlockDropZone, withIsReordering } from './';

const dropBlockZoneWithSelect = compose(
	withBlockName,
	withHasSelectedInnerBlock,
	withSelectedBlock,
	withIsReordering
);

/**
 * Filter drop zones for each block.
 *
 * Disables drop zones within a block while reordering is on.
 *
 * In reorder mode, any interaction with blocks is disabled, and only
 * pages themselves can be dragged & dropped in order to reorder pages within the story.
 *
 * In default mode, only Page will have a drop zone since all the elements are being
 * moved around within a Page.
 *
 * @return {?Function} BlockDropZone or null if reordering.
 */
const withStoryBlockDropZone = () => {
	return dropBlockZoneWithSelect( ( props ) => {
		const { blockName, hasSelectedInnerBlock, isReordering, selectedBlock } = props;

		if ( isReordering ) {
			return null;
		}

		/*
		 * We'll be using only the Page's dropzone since all the elements are being moved around within a Page.
		 * Using dropzone of each single element would result the dropzone moving together with the element.
		 */
		if ( 'amp/amp-story-page' === blockName && hasSelectedInnerBlock && selectedBlock ) {
			return <StoryBlockDropZone srcClientId={ selectedBlock.clientId } srcBlockName={ selectedBlock.name } />;
		}
		return null;
	} );
};

export default withStoryBlockDropZone;
