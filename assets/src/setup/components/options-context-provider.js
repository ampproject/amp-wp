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
 * Context provider for options retrieval and updating.
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
	const [ hasChanges, setHasChanges ] = useState( false );
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
	const saveOptions = useCallback( async () => {
		setSavingOptions( true );

		try {
			await apiFetch( { method: 'post', url: optionsRestEndpoint, data: options } );

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
	}, [ options, optionsRestEndpoint ] );

	/**
	 * Updates options in state.
	 *
	 * @param {Object} Updated options values.
	 */
	const updateOptions = useCallback( ( newOptions ) => {
		if ( false === hasChanges ) {
			setHasChanges( true );
		}

		if ( 'object' === typeof newOptions ) {
			setOptions( { ...options, ...newOptions } );
		}
	}, [ hasChanges, options, setHasChanges, setOptions ] );

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
					hasChanges,
					hasSaved,
					options,
					saveOptions,
					saveOptionsError,
					savingOptions,
					updateOptions,
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
