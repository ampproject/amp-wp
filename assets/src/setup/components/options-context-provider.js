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
export function OptionsContextProvider( { children, optionsKey, optionsRestEndpoint } ) {
	const [ options, setOptions ] = useState( null );
	const [ fetchOptionsError, setFetchOptionsError ] = useState( null );
	const [ savingOptions, setSavingOptions ] = useState( false );
	const [ saveOptionsError, setSaveOptionsError ] = useState( null );
	const [ hasChanges, setHasChanges ] = useState( false );
	const [ hasSaved, setHasSaved ] = useState( false );

	// This component sets state inside async functions. Use this ref to prevent state updates after unmount.
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

			setOptions( fetchedOptions[ optionsKey ] );
		} catch ( e ) {
			if ( true === hasUnmounted.current ) {
				return;
			}

			setFetchOptionsError( e );
		}
	}, [ optionsKey, optionsRestEndpoint ] );

	/**
	 * Sends options to the REST endpoint to be saved.
	 *
	 * @param {Object} data Plugin options to update.
	 */
	const saveOptions = useCallback( async () => {
		setSavingOptions( true );

		try {
			await apiFetch( { method: 'post', url: optionsRestEndpoint, data: { [ optionsKey ]: options } } );

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
	}, [ options, optionsKey, optionsRestEndpoint ] );

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
		if ( ! options ) {
			fetchOptions();
		}
	}, [ fetchOptions, options ] );

	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

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
	optionsKey: PropTypes.string.isRequired,
	optionsRestEndpoint: PropTypes.string.isRequired,
};
