/**
 * WordPress dependencies
 */
import { createContext, useEffect, useState, useRef, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { useError } from '../utils/use-error';

export const User = createContext();

/**
 * Context provider user data.
 *
 * @param {Object} props Component props.
 * @param {?any} props.children Component children.
 * @param {string} props.optionsKey The key of the option to use from the settings endpoint.
 * @param {string} props.optionsRestEndpoint REST endpoint to retrieve options.
 */
export function UserContextProvider( { children, userOptionDeveloperTools, userRestEndpoint } ) {
	const [ user, setUser ] = useState( null );
	const [ fetchingUser, setFetchingUser ] = useState( false );
	const [ savingUserOptions, setSavingUserOptions ] = useState( false );
	const [ hasUserOptionsChanges, setHasUserOptionsChanges ] = useState( false );
	const [ didSaveUserOptions, setDidSaveUserOptions ] = useState( false );

	const { setError } = useError();

	// This component sets state inside async functions. Use this ref to prevent state updates after unmount.
	const hasUnmounted = useRef( false );
	const developerToolsOption = user ? user.meta[ userOptionDeveloperTools ] : null;

	/**
	 * Fetch user options on mount.
	 */
	useEffect( () => {
		if ( ! userRestEndpoint || user || fetchingUser ) {
			return;
		}

		/**
		 * Fetches user data from the REST endpoint for the current user.
		 */
		( async () => {
			setFetchingUser( true );

			try {
				const fetchedUser = await apiFetch( { url: userRestEndpoint } );

				if ( true === hasUnmounted.current ) {
					return;
				}

				setUser( fetchedUser );
			} catch ( e ) {
				setError( e );
				return;
			}

			setFetchingUser( false );
		} )();
	}, [ user, fetchingUser, setError, userRestEndpoint ] );

	/**
	 * Sends user options to the REST endpoint to be saved.
	 */
	const saveUserOptions = useCallback( async () => {
		setSavingUserOptions( true );

		try {
			// To be extra careful, let's only send back meta instead of the entire user data object.
			await apiFetch( { method: 'post', url: userRestEndpoint, data: { meta: user.meta } } );

			if ( true === hasUnmounted.current ) {
				return;
			}
		} catch ( e ) {
			setError( e );
			return;
		}

		setSavingUserOptions( false );
		setDidSaveUserOptions( true );
	}, [ user, setError, setSavingUserOptions, setDidSaveUserOptions, userRestEndpoint ] );

	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	/**
	 * Handles changes to user options.
	 *
	 * @param {Object} newValue AMP user options to update.
	 */
	const setDeveloperToolsOption = ( newValue ) => {
		if ( false === hasUserOptionsChanges ) {
			setHasUserOptionsChanges( true );
		}

		setUser( {
			...user,
			meta: {
				...user.meta,
				[ userOptionDeveloperTools ]: newValue,
			},
		} );

		setDidSaveUserOptions( false );
	};

	return (
		<User.Provider
			value={
				{
					developerToolsOption,
					fetchingUser,
					didSaveUserOptions,
					hasUserOptionsChanges,
					saveUserOptions,
					savingUserOptions,
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
	userOptionDeveloperTools: PropTypes.string.isRequired,
	userRestEndpoint: PropTypes.string.isRequired,
};
