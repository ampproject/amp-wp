/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useState, useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useAPI } from '../../app';
import Context from './context';

const MEDIA = 'media';
const TEXT = 'text';
const SHAPES = 'shapes';
const LINKS = 'links';

function LibraryProvider( { children } ) {
	const { actions: { getMedia } } = useAPI();
	const [ media, setMedia ] = useState( [] );
	const [ perPage, setPerPage ] = useState( 100 );
	const [ mediaType, setMediaType ] = useState( 'image' );
	const [ isMediaLoaded, setIsMediaLoaded ] = useState( false );
	const [ isMediaLoading, setIsMediaLoading ] = useState( false );
	const [ tab, setTab ] = useState( MEDIA );

	const loadMedia = useCallback( () => {
		if ( ! isMediaLoaded && ! isMediaLoading ) {
			setIsMediaLoading( true );
			getMedia( { perPage, mediaType } ).then( ( loadedMedia ) => {
				setIsMediaLoading( false );
				setIsMediaLoaded( true );
				setMedia( loadedMedia );
			} );
		}
	}, [ isMediaLoaded, isMediaLoading, getMedia, perPage, mediaType ] );

	const state = {
		state: {
			tab,
			media,
			isMediaLoading,
			isMediaLoaded,
			mediaType,
		},
		actions: {
			setTab,
			setIsMediaLoading,
			setIsMediaLoaded,
			setPerPage,
			setMediaType,
			loadMedia,
		},
		data: {
			tabs: {
				MEDIA,
				TEXT,
				SHAPES,
				LINKS,
			},
		},
	};

	return (
		<Context.Provider value={ state }>
			{ children }
		</Context.Provider>
	);
}

LibraryProvider.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default LibraryProvider;
