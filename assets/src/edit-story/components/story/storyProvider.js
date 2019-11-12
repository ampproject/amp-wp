/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import Context from './context';

import useLoadStory from './effects/useLoadStory';
import useCurrentPage from './effects/useCurrentPage';

import useAddBlankPage from './actions/useAddBlankPage';

function StoryProvider( { storyId, children } ) {
	const [ pages, setPages ] = useState( [] );
	const [ currentPageId, setCurrentPageById ] = useState( null );
	const [ currentPageNumber, setCurrentPageNumber ] = useState( null );
	const [ currentPage, setCurrentPage ] = useState( null );

	useLoadStory( storyId, setPages, setCurrentPageById );
	useCurrentPage( currentPageId, pages, setCurrentPage, setCurrentPageNumber );

	const addBlankPage = useAddBlankPage( pages, setPages );

	const state = {
		state: {
			pages,
			currentPageId,
			currentPageNumber,
			currentPage,
		},
		actions: {
			setCurrentPageById,
			addBlankPage,
		},
	};

	return (
		<Context.Provider value={ state }>
			{ children }
		</Context.Provider>
	);
}

StoryProvider.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
	storyId: PropTypes.number,
};

export default StoryProvider;
