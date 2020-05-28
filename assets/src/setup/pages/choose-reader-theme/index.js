/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useContext, useRef, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { useInstanceId } from '@wordpress/compose';

/**
 * External dependencies
 */
import { READER_THEMES_ENDPOINT } from 'amp-setup'; // WP data passed via inline script.

/**
 * Internal dependencies
 */
import { Cache } from '../../components/cache-context-provider';
import { Loading } from '../../components/loading';
import { Navigation } from '../../components/navigation-context-provider';
import { Options } from '../../components/options-context-provider';
import { ThemeCard } from './theme-card';

/**
 * Screen for choosing the Reader theme.
 */
export function ChooseReaderTheme() {
	const instanceId = useInstanceId( ChooseReaderTheme );
	const [ themes, setThemes ] = useState( null );
	const [ themeFetchError, setThemeFetchError ] = useState( null );
	const { cacheSet, cacheGet } = useContext( Cache );
	const { canGoForward, setCanGoForward } = useContext( Navigation );
	const { options: { reader_theme: readerTheme } } = useContext( Options );

	// This component sets state inside async functions. Use this ref to prevent state updates after unmount.
	const hasUnmounted = useRef( false );

	/**
	 * Fetches theme data on component mount.
	 */
	useEffect( () => {
		async function fetchThemes() {
			let fetchedThemes = cacheGet( READER_THEMES_ENDPOINT );

			if ( ! fetchedThemes ) {
				try {
					fetchedThemes = await apiFetch( { url: READER_THEMES_ENDPOINT } );
					cacheSet( READER_THEMES_ENDPOINT, fetchedThemes );

					if ( hasUnmounted.current === true ) {
						return;
					}
				} catch ( e ) {
					if ( hasUnmounted.current === true ) {
						return;
					}

					setThemeFetchError( e );
				}
			}

			setThemes( fetchedThemes );
		}

		if ( null === themes ) {
			fetchThemes();
		}
	}, [ cacheSet, cacheGet, themes ] );

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( themes && readerTheme && canGoForward === false ) {
			if ( themes.map( ( { slug } ) => slug ).includes( readerTheme ) ) {
				setCanGoForward( true );
			}
		}
	}, [ canGoForward, setCanGoForward, readerTheme, themes ] );

	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	if ( themeFetchError ) {
		return (
			<p>
				{ __( 'There was an error fetching theme data.', 'amp' ) }
			</p>
		);
	}

	if ( ! themes ) {
		return (
			<Loading />
		);
	}

	return (
		<div className="amp-wp-choose-reader-theme">
			<p>
				{ 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. In placerat justo sed risus viverra, eu viverra ligula tincidunt. Suspendisse finibus sed nisi ac efficitur.' }
			</p>
			<form>
				<ul className="amp-wp-choose-reader-theme__grid">
					{ themes.map( ( theme ) => <ThemeCard key={ `${ instanceId }-${ theme.slug }` } { ...theme } /> ) }
				</ul>
			</form>
		</div>
	);
}
