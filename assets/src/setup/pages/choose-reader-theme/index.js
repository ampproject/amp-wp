/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useContext, useRef, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * External dependencies
 */
import { READER_THEMES_ENDPOINT } from 'amp-setup'; // WP data passed via inline script.

/**
 * Internal dependencies
 */
import { Cache } from '../../components/cache-context-provider';

/**
 * Screen for choosing the Reader theme.
 */
export function ChooseReaderTheme() {
	const [ themes, setThemes ] = useState( null );
	const [ themeFetchError, setThemeFetchError ] = useState( null );
	const { cacheData, getCachedData } = useContext( Cache );

	const hasUnmounted = useRef( false );

	useEffect( () => {
		async function fetchThemes() {
			let fetchedThemes = getCachedData( READER_THEMES_ENDPOINT );

			if ( ! fetchedThemes ) {
				try {
					fetchedThemes = await apiFetch( { url: READER_THEMES_ENDPOINT } );
					cacheData( READER_THEMES_ENDPOINT, fetchedThemes );

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
	}, [ cacheData, getCachedData, themes ] );

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

	return (
		<div>
			{ __( 'Choose Reader Theme', 'amp' ) }
		</div>
	);
}
