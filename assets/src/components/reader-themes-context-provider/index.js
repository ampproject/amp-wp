/**
 * WordPress dependencies
 */
import { createContext, useEffect, useState, useRef, useContext, useMemo } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { USING_FALLBACK_READER_THEME, LEGACY_THEME_SLUG } from 'amp-settings';

/**
 * Internal dependencies
 */
import { useAsyncError } from '../../utils/use-async-error';
import { Options } from '../options-context-provider';
import { ErrorContext } from '../error-context-provider';
import { READER, TRANSITIONAL } from '../../common/constants';

export const ReaderThemes = createContext();

/**
 * Context provider for options retrieval and updating.
 *
 * @param {Object} props Component props.
 * @param {string} props.currentTheme The theme currently active on the site.
 * @param {string} props.wpAjaxUrl WP AJAX URL.
 * @param {?any} props.children Component children.
 * @param {string} props.readerThemesRestPath REST endpoint to fetch reader themes.
 * @param {string} props.updatesNonce Nonce for the AJAX request to install a theme.
 * @param {boolean} props.hasErrorBoundary Whether the component is wrapped in an error boundary.
 * @param {boolean} props.hideCurrentlyActiveTheme Whether the currently active theme should be hidden in the UI.
 */
export function ReaderThemesContextProvider( { wpAjaxUrl, children, currentTheme, hideCurrentlyActiveTheme = false, readerThemesRestPath, updatesNonce, hasErrorBoundary = false } ) {
	const { setAsyncError } = useAsyncError();
	const { error, setError } = useContext( ErrorContext );

	const [ templateModeWasOverridden, setTemplateModeWasOverridden ] = useState( null );
	const [ themeWasOverridden, setThemeWasOverridden ] = useState( false );
	const [ themes, setThemes ] = useState( null );
	const [ fetchingThemes, setFetchingThemes ] = useState( null );
	const [ downloadingTheme, setDownloadingTheme ] = useState( false );
	const [ downloadedTheme, setDownloadedTheme ] = useState( false );
	const [ themesAPIError, setThemesAPIError ] = useState( null );

	const { didSaveOptions, editedOptions, originalOptions, updateOptions, savingOptions } = useContext( Options );

	const { reader_theme: originalReaderTheme, theme_support: originalThemeSupport } = originalOptions;
	const { reader_theme: readerTheme, theme_support: themeSupport } = editedOptions;

	// This component sets state inside async functions. Use this ref to prevent state updates after unmount.
	const hasUnmounted = useRef( false );
	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	/**
	 * The active reader theme.
	 */
	const { originalSelectedTheme, selectedTheme } = useMemo(
		() => {
			const emptyTheme = { name: null, availability: null };
			if ( ! themes ) {
				return {
					originalSelectedTheme: emptyTheme,
					selectedTheme: emptyTheme,
				};
			}

			return {
				originalSelectedTheme: themes.find( ( { slug } ) => slug === originalReaderTheme ) || emptyTheme,
				selectedTheme: themes.find( ( { slug } ) => slug === readerTheme ) || emptyTheme,
			};
		},
		[ originalReaderTheme, readerTheme, themes ],
	);

	/**
	 * Handle downloaded theme errors separately from normal error handling because we don't want it to break the application
	 * after settings have already been saved.
	 */
	const [ downloadingThemeError, setDownloadingThemeError ] = useState( null );

	/**
	 * If the currently selected Reader theme is the same as the active theme, change the template mode from Reader to
	 * Transitional and also set the Reader theme to AMP Legacy.
	 */
	useEffect( () => {
		/**
		 * Set up the initial flag state.
		 */
		if ( templateModeWasOverridden === null ) {
			/**
			 * In case of the non-reader modes, set flag as soon as the mode
			 * becomes available.
			 */
			if ( originalThemeSupport && originalThemeSupport !== READER ) {
				setTemplateModeWasOverridden( false );

				return;
			}

			/**
			 * When dealing with a reader mode, wait for `originalSelectedTheme`
			 * to become available.
			 */
			if ( originalSelectedTheme.availability ) {
				setTemplateModeWasOverridden( false );
			}
		}

		/**
		 * Recheck the flag if and only if the options have been saved.
		 */
		if ( templateModeWasOverridden ) {
			if ( didSaveOptions ) {
				setTemplateModeWasOverridden( false );
			}

			return;
		}

		if ( originalThemeSupport === READER && originalSelectedTheme.availability === 'active' ) {
			updateOptions(
				{
					theme_support: TRANSITIONAL,
					reader_theme: LEGACY_THEME_SLUG,
				},
			);
			setTemplateModeWasOverridden( true );
		}
	}, [ didSaveOptions, originalThemeSupport, originalSelectedTheme.availability, templateModeWasOverridden, updateOptions ] );

	/**
	 * If the currently selected theme is not installable or unavailable for selection, set the Reader theme to AMP Legacy.
	 */
	useEffect( () => {
		if ( themeWasOverridden ) { // Only do this once.
			return;
		}

		if (
			selectedTheme.availability === 'non-installable' ||
			USING_FALLBACK_READER_THEME
		) {
			updateOptions( { reader_theme: LEGACY_THEME_SLUG } );
			setThemeWasOverridden( true );
		}
	}, [ originalSelectedTheme.availability, selectedTheme.availability, themeWasOverridden, updateOptions ] );

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
			if ( downloadingTheme || downloadingThemeError ) {
				return;
			}

			setDownloadingTheme( true );

			try {
				const body = new global.FormData();
				body.append( 'action', 'install-theme' );
				body.append( 'slug', selectedTheme.slug );
				body.append( '_wpnonce', updatesNonce );

				// This is the only fetch request in the setup wizard that doesn't go to a REST endpoint.
				// We need to use window.fetch to bypass the apiFetch middlewares that are useful for other requests.
				/** @type {{ok: boolean}} */
				const response = await global.fetch( wpAjaxUrl, {
					body,
					method: 'POST',
				} );

				if ( true === hasUnmounted.current ) {
					return;
				}

				if ( ! response.ok ) {
					throw new Error( __( 'Reader theme failed to download.', 'amp' ) );
				}

				setDownloadedTheme( selectedTheme.slug );
			} catch ( e ) {
				if ( true === hasUnmounted.current ) {
					return;
				}

				setDownloadingThemeError( e );
			}

			setDownloadingTheme( false );
		} )();
	}, [ wpAjaxUrl, downloadingTheme, downloadingThemeError, savingOptions, selectedTheme, themeSupport, updatesNonce ] );

	/**
	 * Fetches theme data when needed.
	 */
	useEffect( () => {
		if ( fetchingThemes ) {
			return;
		}

		if ( error || ! readerThemesRestPath || themes || READER !== themeSupport ) {
			setFetchingThemes( false );
			return;
		}

		/**
		 * Fetch themes from the REST endpoint.
		 */
		( async () => {
			setFetchingThemes( true );

			try {
				/** @type {{headers: Headers, json: Function}} */
				const fetchedThemesResponse = await apiFetch( { path: readerThemesRestPath, parse: false } );

				setThemesAPIError( fetchedThemesResponse.headers.get( 'X-AMP-Theme-API-Error' ) );

				const fetchedThemes = await fetchedThemesResponse.json();

				if ( hasUnmounted.current === true ) {
					return;
				}

				// Screenshots are required.
				setThemes( fetchedThemes );
			} catch ( e ) {
				if ( hasUnmounted.current === true ) {
					return;
				}

				setError( e );

				if ( hasErrorBoundary ) {
					setAsyncError( e );
				}
				return;
			}

			setFetchingThemes( false );
		} )();
	}, [ error, hasErrorBoundary, fetchingThemes, readerThemesRestPath, setAsyncError, setError, themes, themeSupport ] );

	const { filteredThemes } = useMemo( () => {
		let newFilteredThemes;

		if ( hideCurrentlyActiveTheme ) {
			newFilteredThemes = ( themes || [] ).filter( ( theme ) => {
				return 'active' !== theme.availability;
			} );
		} else {
			newFilteredThemes = themes;
		}

		return { filteredThemes: newFilteredThemes };
	}, [ hideCurrentlyActiveTheme, themes ] );

	/**
	 * Separate available themes (both installed and installable) from those
	 * that need to be installed manually.
	 */
	const { availableThemes, unavailableThemes } = useMemo(
		() => ( filteredThemes || [] ).reduce(
			( collections, theme ) => {
				if ( theme.availability === 'non-installable' ) {
					collections.unavailableThemes.push( theme );
				} else {
					collections.availableThemes.push( theme );
				}

				return collections;
			},
			{ availableThemes: [], unavailableThemes: [] },
		),
		[ filteredThemes ],
	);

	return (
		<ReaderThemes.Provider
			value={
				{
					availableThemes,
					currentTheme,
					downloadedTheme,
					downloadingTheme,
					downloadingThemeError,
					fetchingThemes,
					selectedTheme: selectedTheme || {},
					templateModeWasOverridden,
					themes: filteredThemes,
					themesAPIError,
					unavailableThemes,
				}
			}
		>
			{ children }
		</ReaderThemes.Provider>
	);
}

ReaderThemesContextProvider.propTypes = {
	children: PropTypes.any,
	currentTheme: PropTypes.shape( {
		name: PropTypes.string.isRequired,
	} ).isRequired,
	hasErrorBoundary: PropTypes.bool,
	readerThemesRestPath: PropTypes.string.isRequired,
	hideCurrentlyActiveTheme: PropTypes.bool,
	updatesNonce: PropTypes.string.isRequired,
	wpAjaxUrl: PropTypes.string.isRequired,
};
