/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { createPage } from '../../../elements';

// If page id is changed, load current page and set page number.
function useAddBlankPage( {
	pages,
	setPages,
	clearSelection,
} ) {
	const addBlankPage = useCallback( () => {
		const newPage = createPage( { index: pages.length } );
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
