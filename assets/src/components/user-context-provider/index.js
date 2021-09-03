/**
 * WordPress dependencies
 */
import { createContext, useEffect, useState, useRef, useCallback, useMemo, useContext } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { useAsyncError } from '../../utils/use-async-error';
import { Options } from '../options-context-provider';

export const User = createContext();

/**
 * Context provider user data.
 *
 * @param {Object}  props                           Component props.
 * @param {?any}    props.children                  Component children.
 * @param {boolean} props.allowConfiguredPluginOnly Provide only if the plugin has been configured (Onboarding Wizard complete).
 * @param {string}  props.userOptionDeveloperTools  The key of the option to use from the settings endpoint.
 * @param {string}  props.userRestPath              REST endpoint to retrieve options.
 */
export function UserContextProvider( { children, allowConfiguredPluginOnly = false, userOptionDeveloperTools, userRestPath } ) {
	const { originalOptions, fetchingOptions } = useContext( Options );
	const { plugin_configured: pluginConfigured } = originalOptions;
	const [ fetchingUser, setFetchingUser ] = useState( false );
	const [ developerToolsOption, setDeveloperToolsOption ] = useState( null );
	const [ originalDeveloperToolsOption, setOriginalDeveloperToolsOption ] = useState( null );
	const [ savingDeveloperToolsOption, setSavingDeveloperToolsOption ] = useState( false );
	const [ didSaveDeveloperToolsOption, setDidSaveDeveloperToolsOption ] = useState( false );
	const { setAsyncError } = useAsyncError();

	// This component sets state inside async functions. Use this ref to prevent state updates after unmount.
	const hasUnmounted = useRef( false );
	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	const hasDeveloperToolsOptionChange = useMemo(
		() => null !== developerToolsOption && developerToolsOption !== originalDeveloperToolsOption,
		[ developerToolsOption, originalDeveloperToolsOption ],
	);
	/**
	 * Fetch user options on mount.
	 */
	useEffect( () => {
		if ( fetchingOptions ) {
			return;
		}

		if ( ! pluginConfigured && allowConfiguredPluginOnly ) {
			setOriginalDeveloperToolsOption( null );
			setDeveloperToolsOption( null );
			return;
		}

		if ( ! userRestPath || fetchingUser || null !== originalDeveloperToolsOption ) {
			return;
		}

		/**
		 * Fetches user data from the REST endpoint for the current user.
		 */
		( async () => {
			setFetchingUser( true );

			try {
				const fetchedUser = await apiFetch( { path: userRestPath } );

				if ( true === hasUnmounted.current ) {
					return;
				}

				setOriginalDeveloperToolsOption( fetchedUser[ userOptionDeveloperTools ] );
				setDeveloperToolsOption( fetchedUser[ userOptionDeveloperTools ] );
			} catch ( e ) {
				setAsyncError( e );
				return;
			}

			setFetchingUser( false );
		} )();
	}, [ allowConfiguredPluginOnly, fetchingOptions, fetchingUser, originalDeveloperToolsOption, pluginConfigured, setAsyncError, userOptionDeveloperTools, userRestPath ] );

	/**
	 * Sends the option back to the REST endpoint to be saved.
	 */
	const saveDeveloperToolsOption = useCallback( async () => {
		if ( ! hasDeveloperToolsOptionChange ) {
			return;
		}

		setSavingDeveloperToolsOption( true );

		try {
			const fetchedUser = await apiFetch( { method: 'post', path: userRestPath, data: { [ userOptionDeveloperTools ]: developerToolsOption } } );

			if ( true === hasUnmounted.current ) {
				return;
			}

			setOriginalDeveloperToolsOption( fetchedUser[ userOptionDeveloperTools ] );
			setDeveloperToolsOption( fetchedUser[ userOptionDeveloperTools ] );
		} catch ( e ) {
			setAsyncError( e );
			return;
		}

		setDidSaveDeveloperToolsOption( true );
		setSavingDeveloperToolsOption( false );
	}, [ hasDeveloperToolsOptionChange, developerToolsOption, setAsyncError, userOptionDeveloperTools, userRestPath ] );

	return (
		<User.Provider
			value={
				{
					developerToolsOption,
					fetchingUser,
					didSaveDeveloperToolsOption,
					hasDeveloperToolsOptionChange,
					originalDeveloperToolsOption,
					saveDeveloperToolsOption,
					savingDeveloperToolsOption,
					setDeveloperToolsOption,
				}
			}
		>
			{ children }
		</User.Provider>
	);
}

UserContextProvider.propTypes = {
	children: PropTypes.any,
	allowConfiguredPluginOnly: PropTypes.bool,
	userOptionDeveloperTools: PropTypes.string.isRequired,
	userRestPath: PropTypes.string.isRequired,
};
