/**
 * WordPress dependencies
 */
import { createContext, useEffect, useState, useRef, useContext, useMemo } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { useError } from '../utils/use-error';
import { Options } from './options-context-provider';

export const ReaderThemes = createContext();

/**
 * Context provider for options retrieval and updating.
 *
 * @param {Object} props Component props.
 * @param {string} props.wpAjaxUrl WP AJAX URL.
 * @param {?any} props.children Component children.
 * @param {string} props.readerThemesEndpoint REST endpoint to fetch reader themes.
 * @param {string} props.updatesNonce Nonce for the AJAX request to install a theme.
 */
export function ReaderThemesContextProvider( { wpAjaxUrl, children, readerThemesEndpoint, updatesNonce } ) {
	const [ themes, setThemes ] = useState( null );
	const [ fetchingThemes, setFetchingThemes ] = useState( false );
	const [ downloadingTheme, setDownloadingTheme ] = useState( false );

	const { setError } = useError();

	const { options, savingOptions } = useContext( Options );
	const { reader_theme: readerTheme } = options || {};

	// This component sets state inside async functions. Use this ref to prevent state updates after unmount.
	const hasUnmounted = useRef( false );

	const selectedTheme = useMemo(
		() => themes ? themes.find( ( { slug } ) => slug === readerTheme ) : null,
		[ readerTheme, themes ],
	);

	/**
	 * Downloads the selected reader theme, if necessary, when options are saved.
	 */
	useEffect( () => {
		if ( ! selectedTheme ) {
			return;
		}

		if ( ! savingOptions || downloadingTheme ) {
			return;
		}

		if ( 'installable' !== selectedTheme.availability ) {
			return;
		}

		/**
		 * Downloads a theme from WordPress.org using the traditional AJAX action.
		 */
		( async () => {
			setDownloadingTheme( true );

			try {
				const body = new global.FormData();
				body.append( 'action', 'install-theme' );
				body.append( 'slug', selectedTheme.slug );
				body.append( '_wpnonce', updatesNonce );

				// This is the only fetch request in the setup wizard that doesn't go to a REST endpoint.
				// We need to use window.fetch to bypass the apiFetch middlewares that are useful for other requests.
				await global.fetch( wpAjaxUrl, {
					body,
					method: 'POST',
				} );

				if ( true === hasUnmounted.current ) {
					return;
				}
			} catch ( e ) {
				setError( e );
				return;
			}

			setDownloadingTheme( false );
		} )();
	}, [ wpAjaxUrl, downloadingTheme, savingOptions, selectedTheme, setError, updatesNonce ] );

	/**
	 * Fetches theme data on component mount.
	 */
	useEffect( () => {
		if ( fetchingThemes || ! readerThemesEndpoint || themes ) {
			return;
		}

		/**
		 * Fetch themes from the REST endpoint.
		 */
		( async () => {
			setFetchingThemes( true );

			try {
				const fetchedThemes = await apiFetch( { url: addQueryArgs( readerThemesEndpoint, { 'amp-new-onboarding': '1' } ) } );

				if ( hasUnmounted.current === true ) {
					return;
				}

				// Screenshots are required.
				setThemes( fetchedThemes );
			} catch ( e ) {
				setError( e );
				return;
			}

			setFetchingThemes( false );
		} )();
	}, [ fetchingThemes, readerThemesEndpoint, setError, themes ] );

	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	return (
		<ReaderThemes.Provider
			value={
				{
					downloadingTheme,
					fetchingThemes,
					themes,
				}
			}
		>
			{ children }
		</ReaderThemes.Provider>
	);
}

ReaderThemesContextProvider.propTypes = {
	wpAjaxUrl: PropTypes.string.isRequired,
	children: PropTypes.any,
	readerThemesEndpoint: PropTypes.string.isRequired,
	updatesNonce: PropTypes.string.isRequired,
};
