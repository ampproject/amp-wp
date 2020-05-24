/**
 * WordPress dependencies
 */
import { createContext, useEffect, useState, useRef, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

export const Options = createContext();

/**
 * Context provider for options fetching and retrieval.
 *
 * @param {Object} props Component props.
 * @param {?any} props.children Component children.
 * @param {string} props.optionsRestEndpoint REST endpoint to retrieve options.
 */
export function OptionsContextProvider( { children, optionsRestEndpoint } ) {
	const [ options, setOptions ] = useState( null );
	const [ fetchOptionsError, setFetchOptionsError ] = useState( null );
	const [ savingOptions, setSavingOptions ] = useState( false );
	const [ saveOptionsError, setSaveOptionsError ] = useState( null );
	const [ hasSaved, setHasSaved ] = useState( false );

	const hasUnmounted = useRef( false );

	/**
	 * Fetches plugin options from the REST endpoint.
	 */
	const fetchOptions = useCallback( async () => {
		let fetchedOptions;

		try {
			fetchedOptions = await apiFetch( { url: optionsRestEndpoint } );

			if ( true === hasUnmounted.current ) {
				return;
			}

			setOptions( fetchedOptions );
		} catch ( e ) {
			if ( true === hasUnmounted.current ) {
				return;
			}

			setFetchOptionsError( e );
		}
	}, [ optionsRestEndpoint ] );

	/**
	 * Sends options to the REST endpoint to be saved.
	 *
	 * @param {Object} data Plugin options to update.
	 */
	const saveOptions = useCallback( async ( data ) => {
		setSavingOptions( true );

		try {
			await apiFetch( { method: 'post', url: optionsRestEndpoint, data } );

			if ( true === hasUnmounted.current ) {
				return;
			}
		} catch ( e ) {
			if ( true === hasUnmounted.current ) {
				return;
			}

			setSaveOptionsError( e );
		}

		setSavingOptions( false );
		setHasSaved( true );
	}, [ optionsRestEndpoint ] );

	useEffect( () => {
		fetchOptions();

		return () => {
			hasUnmounted.current = true;
		};
	}, [ fetchOptions ] );

	return (
		<Options.Provider
			value={
				{
					fetchingOptions: null === options,
					fetchOptionsError,
					hasSaved,
					options,
					saveOptions,
					saveOptionsError,
					savingOptions,
				}
			}
		>
			{ children }
		</Options.Provider>
	);
}

OptionsContextProvider.propTypes = {
	children: PropTypes.any,
	optionsRestEndpoint: PropTypes.string.isRequired,
};
