/**
 * WordPress dependencies
 */
import { createContext, useEffect, useState, useRef, useContext, useMemo } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';
/**
 * Internal dependencies
 */
import { Options } from './options-context-provider';

export const ReaderThemes = createContext();

/**
 * Context provider for options retrieval and updating.
 *
 * @param {Object} props Component props.
 * @param {string} props.ajaxurl WP AJAX URL.
 * @param {?any} props.children Component children.
 * @param {string} props.readerThemesEndpoint REST endpoint to fetch reader themes.
 * @param {string} props.updatesNonce Nonce for the AJAX request to install a theme.
 */
export function ReaderThemesContextProvider( { ajaxurl, children, readerThemesEndpoint, updatesNonce } ) {
	const [ themes, setThemes ] = useState( null );
	const [ fetchingThemes, setFetchingThemes ] = useState( false );
	const [ themeFetchError, setThemeFetchError ] = useState( null );
	const [ downloadingTheme, setDownloadingTheme ] = useState( false );
	const [ downloadingThemeError, setDownloadingThemeError ] = useState( null );

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

		if ( 'installable' !== selectedTheme ) {
			return;
		}

		/**
		 * Downloads a theme from WordPress.org using the traditional AJAX action.
		 */
		async function downloadReaderTheme() {
			setDownloadingTheme( true );

			try {
				const body = new global.FormData();
				body.append( 'action', 'install-theme' );
				body.append( 'slug', selectedTheme.slug );
				body.append( '_wpnonce', updatesNonce );

				// This is the only fetch request in the setup wizard that doesn't go to a REST endpoint.
				// We need to use window.fetch to bypass the apiFetch middlewares that are useful for other requests.
				await global.fetch( ajaxurl, {
					body,
					method: 'POST',
				} );

				if ( true === hasUnmounted.current ) {
					return;
				}
			} catch ( e ) {
				if ( true === hasUnmounted.current ) {
					return;
				}

				setDownloadingThemeError( e );
			}

			setDownloadingTheme( false );
		}

		downloadReaderTheme();
	}, [ ajaxurl, downloadingTheme, savingOptions, selectedTheme, updatesNonce ] );

	/**
	 * Fetches theme data on component mount.
	 */
	useEffect( () => {
		async function fetchThemes() {
			setFetchingThemes( true );

			try {
				const fetchedThemes = await apiFetch( { url: readerThemesEndpoint } );

				if ( hasUnmounted.current === true ) {
					return;
				}

				// Screenshots are required.
				setThemes( fetchedThemes.filter( ( theme ) => theme.screenshot_url ) );
			} catch ( e ) {
				if ( hasUnmounted.current === true ) {
					return;
				}

				setThemeFetchError( e );
			}

			setFetchingThemes( false );
		}

		if ( readerThemesEndpoint && ! themes && ! fetchingThemes && ! themeFetchError ) {
			fetchThemes();
		}
	}, [ fetchingThemes, readerThemesEndpoint, themes, themeFetchError ] );

	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	return (
		<ReaderThemes.Provider
			value={
				{
					downloadingTheme,
					fetchingThemes,
					themeFetchError,
					themes,
				}
			}
		>
			{ children }
		</ReaderThemes.Provider>
	);
}

ReaderThemesContextProvider.propTypes = {
	ajaxurl: PropTypes.string.isRequired,
	children: PropTypes.any,
	readerThemesEndpoint: PropTypes.string.isRequired,
	updatesNonce: PropTypes.string.isRequired,
};
