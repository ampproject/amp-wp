/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';
import { createBlock } from '@wordpress/blocks';

// If page id is changed, load current page and set page number.
function useAddBlankPage( {
	pages,
	setPages,
	clearSelection,
} ) {
	const addBlankPage = useCallback( () => {
		const newPage = createBlock( 'amp/amp-story-page' );
		const newPages = [
			...pages,
			newPage,
		];
		setPages( newPages );
		clearSelection();
		return newPage;
	}, [ pages, setPages, clearSelection ] );
	return addBlankPage;
}

export default useAddBlankPage;
