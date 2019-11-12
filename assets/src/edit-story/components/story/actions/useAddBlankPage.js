/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';
import { createBlock } from '@wordpress/blocks';

// When ID is set, load story from API.
function useAddBlankPage( pages, setPages ) {// If page id is changed, load current page and set page number.
	const addBlankPage = useCallback( () => {
		const newPage = createBlock( 'amp/amp-story-page' );
		const newPages = [
			...pages,
			newPage,
		];
		setPages( newPages );
		return newPage;
	}, [ pages, setPages ] );
	return addBlankPage;
}

export default useAddBlankPage;
