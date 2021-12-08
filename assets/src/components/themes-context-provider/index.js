/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createContext, useEffect, useRef, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export const Themes = createContext();

/**
 * Themes context provider.
 *
 * @param {Object} props          Component props.
 * @param {any}    props.children Component children.
 */
export function ThemesContextProvider( { children } ) {
	const [ themes, setThemes ] = useState( [] );
	const [ fetchingThemes, setFetchingThemes ] = useState( null );
	const [ error, setError ] = useState();

	/**
	 * This component sets state inside async functions.
	 * Use this ref to prevent state updates after unmount.
	 */
	const hasUnmounted = useRef( false );
	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	/**
	 * Fetches the themes data.
	 */
	useEffect( () => {
		if ( error || themes.length > 0 || fetchingThemes ) {
			return;
		}

		( async () => {
			setFetchingThemes( true );

			try {
				const fetchedThemes = await apiFetch( {
					path: '/wp/v2/themes?status=active',
				} );

				if ( hasUnmounted.current === true ) {
					return;
				}

				setThemes( fetchedThemes );
			} catch ( e ) {
				if ( hasUnmounted.current === true ) {
					return;
				}

				setError( e );
			}

			setFetchingThemes( false );
		} )();
	}, [ error, fetchingThemes, themes ] );

	return (
		<Themes.Provider
			value={ {
				fetchingThemes,
				themes,
			} }
		>
			{ children }
		</Themes.Provider>
	);
}
ThemesContextProvider.propTypes = {
	children: PropTypes.any,
};
