/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createContext, useState, useEffect, useContext } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { ErrorContext } from '../error-context-provider';
import { useAsyncError } from '../../utils/use-async-error';

export const SiteSettings = createContext();

/**
 * Site settings context provider.
 *
 * @param {Object} props Component props.
 * @param {any} props.children Component children.
 * @param {boolean} props.hasErrorBoundary Whether the component is wrapped in an error boundary.
 */
export function SiteSettingsProvider( { children, hasErrorBoundary = false } ) {
	const [ settings, setSettings ] = useState( {} );
	const [ fetchingSiteSettings, setFetchingSiteSettings ] = useState( false );

	const { error, setError } = useContext( ErrorContext );
	const { setAsyncError } = useAsyncError();

	useEffect( () => {
		if ( error || Object.keys( settings ).length || fetchingSiteSettings ) {
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

				setError( e );

				if ( hasErrorBoundary ) {
					setAsyncError( e );
				}
				return;
			}

			setFetchingSiteSettings( false );
		} )();

		return () => {
			unmounted = true;
		};
	}, [ error, settings, fetchingSiteSettings, setError, hasErrorBoundary, setAsyncError ] );

	return (
		<SiteSettings.Provider value={ { settings, fetchingSiteSettings } }>
			{ children }
		</SiteSettings.Provider>
	);
}

SiteSettingsProvider.propTypes = {
	children: PropTypes.any,
	hasErrorBoundary: PropTypes.bool,
};
