/**
 * WordPress dependencies
 */
import { createContext, useEffect, useState, useRef, useCallback, useMemo } from '@wordpress/element';
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
 * @param {string} props.userOptionDeveloperTools The key of the option to use from the settings endpoint.
 * @param {string} props.userRestEndpoint REST endpoint to retrieve options.
 */
export function UserContextProvider( { children, userOptionDeveloperTools, userRestEndpoint } ) {
	const [ user, setUser ] = useState( null );
	const [ fetchingUser, setFetchingUser ] = useState( false );
	const [ developerToolsOption, setDeveloperToolsOption ] = useState( null );
	const [ savingDeveloperToolsOption, setSavingDeveloperToolsOption ] = useState( false );
	const [ didSaveDeveloperToolsOption, setDidSaveDeveloperToolsOption ] = useState( false );

	const { setError } = useError();

	// This component sets state inside async functions. Use this ref to prevent state updates after unmount.
	const hasUnmounted = useRef( false );

	const hasDeveloperToolsOptionChange = useMemo(
		() => user && developerToolsOption !== user[ userOptionDeveloperTools ],
		[ user, developerToolsOption, userOptionDeveloperTools ],
	);

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

				setDeveloperToolsOption( fetchedUser[ userOptionDeveloperTools ] );
				setUser( fetchedUser );
			} catch ( e ) {
				setError( e );
				return;
			}

			setFetchingUser( false );
		} )();
	}, [ user, fetchingUser, setError, userOptionDeveloperTools, userRestEndpoint ] );

	/**
	 * Sends the option back to the REST endpoint to be saved.
	 */
	const saveDeveloperToolsOption = useCallback( async () => {
		if ( ! user ) {
			return;
		}

		setSavingDeveloperToolsOption( true );

		try {
			await apiFetch( { method: 'post', url: userRestEndpoint, data: { [ userOptionDeveloperTools ]: developerToolsOption } } );

			if ( true === hasUnmounted.current ) {
				return;
			}
		} catch ( e ) {
			setError( e );
			return;
		}

		setSavingDeveloperToolsOption( false );
		setDidSaveDeveloperToolsOption( true );
	}, [ user, developerToolsOption, setError, setSavingDeveloperToolsOption, setDidSaveDeveloperToolsOption, userOptionDeveloperTools, userRestEndpoint ] );

	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	return (
		<User.Provider
			value={
				{
					developerToolsOption,
					fetchingUser,
					didSaveDeveloperToolsOption,
					hasDeveloperToolsOptionChange,
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
	userOptionDeveloperTools: PropTypes.string.isRequired,
	userRestEndpoint: PropTypes.string.isRequired,
};
