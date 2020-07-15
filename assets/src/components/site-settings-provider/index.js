/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createContext, useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export const SiteSettings = createContext();

/**
 * Site settings context provider.
 *
 * @param {Object} props Component props.
 * @param {any} props.children Component children.
 */
export function SiteSettingsProvider( { children } ) {
	const [ settings, setSettings ] = useState( {} );
	const [ fetchingSiteSettings, setFetchingSiteSettings ] = useState( false );
	const [ fetchSiteSettingsError, setFetchingSiteSettingsError ] = useState( null );

	useEffect( () => {
		if ( Object.keys( settings ).length || fetchSiteSettingsError || fetchingSiteSettings ) {
			return () => undefined;
		}

		let unmounted = false;

		( async () => {
			try {
				const fetchedSiteSettings = await apiFetch( { path: '/wp/v2/settings' } );

				if ( unmounted ) {
					return;
				}

				setSettings( fetchedSiteSettings );
			} catch ( e ) {
				if ( unmounted ) {
					return;
				}

				setFetchingSiteSettingsError( e );
			}

			setFetchingSiteSettings( false );
		} )();

		return () => {
			unmounted = true;
		};
	}, [ settings, fetchSiteSettingsError, fetchingSiteSettings ] );

	return (
		<SiteSettings.Provider value={ { settings, fetchingSiteSettings, fetchSiteSettingsError } }>
			{ children }
		</SiteSettings.Provider>
	);
}

SiteSettingsProvider.propTypes = {
	children: PropTypes.any,
};
