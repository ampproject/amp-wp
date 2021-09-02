/**
 * WordPress dependencies
 */
import { createContext, useEffect, useState, useRef, useCallback, useContext } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { useAsyncError } from '../../utils/use-async-error';
import { ErrorContext } from '../error-context-provider';

export const Options = createContext();

/**
 * Returns a promise that resolves after one second.
 */
function waitASecond() {
	return new Promise( ( resolve ) => {
		setTimeout( resolve, 1000 );
	} );
}

/**
 * Context provider for options retrieval and updating.
 *
 * @param {Object}  props                       Component props.
 * @param {?any}    props.children              Component children.
 * @param {string}  props.optionsRestPath       REST endpoint to retrieve options.
 * @param {boolean} props.populateDefaultValues Whether default values should be populated.
 * @param {boolean} props.hasErrorBoundary      Whether the component is wrapped in an error boundary.
 * @param {boolean} props.delaySave             Whether to delay updating state when saving data.
 */
export function OptionsContextProvider( { children, optionsRestPath, populateDefaultValues, hasErrorBoundary = false, delaySave = false } ) {
	const [ updates, setUpdates ] = useState( {} );
	const [ fetchingOptions, setFetchingOptions ] = useState( null );
	const [ savingOptions, setSavingOptions ] = useState( false );
	const [ didSaveOptions, setDidSaveOptions ] = useState( false );
	const [ originalOptions, setOriginalOptions ] = useState( {} );

	const { error, setError } = useContext( ErrorContext );
	const { setAsyncError } = useAsyncError();

	const [ readerModeWasOverridden, setReaderModeWasOverridden ] = useState( false );

	// This component sets state inside async functions. Use this ref to prevent state updates after unmount.
	const hasUnmounted = useRef( false );
	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	/**
	 * Fetches options.
	 */
	useEffect( () => {
		if ( error || Object.keys( originalOptions ).length || fetchingOptions ) {
			return;
		}

		/**
		 * Fetches plugin options from the REST endpoint.
		 */
		( async () => {
			setFetchingOptions( true );

			try {
				const fetchedOptions = await apiFetch( { path: optionsRestPath } );

				if ( true === hasUnmounted.current ) {
					return;
				}

				if ( ! populateDefaultValues && fetchedOptions.plugin_configured === false ) {
					fetchedOptions.mobile_redirect = true;
					fetchedOptions.reader_theme = null;
					fetchedOptions.theme_support = null;
				}

				setOriginalOptions( fetchedOptions );
			} catch ( e ) {
				if ( true === hasUnmounted.current ) {
					return;
				}

				setError( e );

				if ( hasErrorBoundary ) {
					setAsyncError( e );
				}
				return;
			}

			setFetchingOptions( false );
		} )();
	}, [ error, fetchingOptions, hasErrorBoundary, originalOptions, optionsRestPath, populateDefaultValues, setAsyncError, setError ] );

	/**
	 * Sends options to the REST endpoint to be saved.
	 *
	 * @param {Object} data Plugin options to update.
	 */
	const saveOptions = useCallback( async () => {
		setSavingOptions( true );

		try {
			const updatesToSave = { ...updates };

			// If the reader theme was set to null on initialization (i.e., this is the first time through the wizard
			// and reader mode was selected), remove it from the updates.
			if ( null === updatesToSave.reader_theme ) {
				delete updatesToSave.reader_theme;
			}

			// If this is the first time running the wizard and mobile_redirect is not in updates, set mobile_redirect to true.
			// We do this here instead of in the fetch effect to prevent the exit confirmation before the user has interacted.
			if ( ! originalOptions.plugin_configured && ! ( 'mobile_redirect' in updatesToSave ) ) {
				updatesToSave.mobile_redirect = originalOptions.mobile_redirect;
			}

			if ( ! originalOptions.plugin_configured ) {
				updatesToSave.plugin_configured = true;
			}

			// Ensure this promise lasts at least a second so that the "Saving Options" load screen is
			// visible long enough for the user to see it is happening.
			const [ savedOptions ] = await Promise.all(
				[
					apiFetch(
						{
							method: 'post',
							path: optionsRestPath,
							data: updatesToSave,
						},
					),
					delaySave ? waitASecond() : () => undefined,
				],
			);

			if ( true === hasUnmounted.current ) {
				return;
			}

			setOriginalOptions( savedOptions );
			setError( null );
		} catch ( e ) {
			if ( true === hasUnmounted.current ) {
				return;
			}

			setSavingOptions( false );
			setError( e );

			if ( hasErrorBoundary ) {
				setAsyncError( e );
			}

			return;
		}

		setUpdates( {} );
		setDidSaveOptions( true );
		setSavingOptions( false );
	}, [ delaySave, hasErrorBoundary, optionsRestPath, setAsyncError, originalOptions, setError, updates ] );

	/**
	 * Updates options in state.
	 *
	 * @param {Object} newOptions Updated options values.
	 */
	const updateOptions = useCallback( ( newOptions ) => {
		setUpdates( { ...updates, ...newOptions } );
		setDidSaveOptions( false );
	}, [ updates ] );

	// Allows an item in the updates object to be removed.
	const unsetOption = useCallback( ( option ) => {
		const newOptions = { ...updates };
		delete newOptions[ option ];
		setUpdates( newOptions );
	}, [ updates ] );

	return (
		<Options.Provider
			value={
				{
					editedOptions: { ...originalOptions, ...updates },
					fetchingOptions,
					hasOptionsChanges: Boolean( Object.keys( updates ).length ),
					didSaveOptions,
					updates,
					originalOptions,
					saveOptions,
					savingOptions,
					unsetOption,
					updateOptions,
					readerModeWasOverridden,
					setReaderModeWasOverridden,
				}
			}
		>
			{ children }
		</Options.Provider>
	);
}

OptionsContextProvider.propTypes = {
	children: PropTypes.any,
	delaySave: PropTypes.bool,
	hasErrorBoundary: PropTypes.bool,
	optionsRestPath: PropTypes.string.isRequired,
	populateDefaultValues: PropTypes.bool.isRequired,
};
