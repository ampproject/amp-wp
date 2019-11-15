/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { useConfig } from '../';
import Context from './context';

function APIProvider( { children } ) {
	const { api: { stories, media } } = useConfig();

	const getStoryById = useCallback(
		( storyId ) => apiFetch( { path: `${ stories }/${ storyId }?context=edit` } ),
		[ stories ],
	);

	const getMedia = useCallback(
		() => apiFetch( { path: `${ media }/` } )
			.then( ( data ) => data.map( ( { guid: { rendered: src } } ) => ( { src } ) ) ),
		[ media ],
	);

	const state = {
		actions: {
			getStoryById,
			getMedia,
		},
	};

	return (
		<Context.Provider value={ state }>
			{ children }
		</Context.Provider>
	);
}

APIProvider.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default APIProvider;
