/**
 * WordPress dependencies
 */
import { createContext, useEffect, useState, useRef, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

export const User = createContext();

/**
 * Context provider user data.
 *
 * @param {Object} props Component props.
 * @param {?any} props.children Component children.
 * @param {string} props.optionsKey The key of the option to use from the settings endpoint.
 * @param {string} props.optionsRestEndpoint REST endpoint to retrieve options.
 */
export function UserContextProvider( {
	children, userOptionsKey, userOptionDeveloperTools, userRestEndpoint } ) {
	const [ user, setUser ] = useState( null );
	const [ fetchingUser, setFetchingUser ] = useState( false );
	const [ fetchUserError, setFetchUserError ] = useState( null );
	const [ savingUserOptions, setSavingUserOptions ] = useState( false );
	const [ saveUserOptionsError, setSaveUserOptionsError ] = useState( null );
	const [ hasUserOptionsChanges, setHasUserOptionsChanges ] = useState( false );
	const [ hasSavedUserOptions, setHasSavedUserOptions ] = useState( false );

	// This component sets state inside async functions. Use this ref to prevent state updates after unmount.
	const hasUnmounted = useRef( false );

	const options = user && 'meta' in user ? user.meta[ userOptionsKey ] : {};
	const developerToolsOption = options[ userOptionDeveloperTools ];

	/**
	 * Fetch user options on mount.
	 */
	useEffect( () => {
		if ( ! userRestEndpoint || user || fetchingUser || fetchUserError ) {
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
				if ( true === hasUnmounted.current ) {
					return;
				}

				setFetchUserError( e );
			}

			setFetchingUser( false );
		} )();
	}, [ user, fetchingUser, fetchUserError, userRestEndpoint ] );

	/**
	 * Sends user options to the REST endpoint to be saved.
	 */
	const saveUserOptions = useCallback( async () => {
		setSavingUserOptions( true );

		try {
			await apiFetch( { method: 'post', url: userRestEndpoint, data: user } );

			if ( true === hasUnmounted.current ) {
				return;
			}
		} catch ( e ) {
			if ( true === hasUnmounted.current ) {
				return;
			}

			setSaveUserOptionsError( e );
		}

		setSavingUserOptions( false );
		setHasUserOptionsChanges( true );
	}, [ user, setSaveUserOptionsError, setSavingUserOptions, setHasUserOptionsChanges, userRestEndpoint ] );

	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	const setDeveloperToolsOption = ( newValue ) => {
		setUser( {
			...user,
			meta: {
				...user.meta,
				[ userOptionsKey ]: {
					...user.meta[ userOptionsKey ],
					[ userOptionDeveloperTools ]: newValue,
				},
			},
		} );
	};

	return (
		<User.Provider
			value={
				{
					developerToolsOption,
					fetchingUser,
					fetchUserError,
					saveUserOptions,
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
	userOptionsKey: PropTypes.string.isRequired,
	userRestEndpoint: PropTypes.string.isRequired,
};
