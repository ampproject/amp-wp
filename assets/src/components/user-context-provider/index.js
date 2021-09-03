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
 * @param {Object}  props                                              Component props.
 * @param {?any}    props.children                                     Component children.
 * @param {boolean} props.onlyFetchIfPluginIsConfigured                Flag indicating whether the users data should be fetched only if the plugin is fully configured (i.e. the Onboarding Wizard has been completed).
 * @param {string}  props.userFieldReviewPanelDismissedForTemplateMode The key of the option to use from the settings endpoint.
 * @param {string}  props.userOptionDeveloperTools                     The key of the option to use from the settings endpoint.
 * @param {string}  props.usersResourceRestPath                        The REST path for interacting with the `users` resources.
 */
export function UserContextProvider( {
	children,
	onlyFetchIfPluginIsConfigured = true,
	userFieldReviewPanelDismissedForTemplateMode,
	userOptionDeveloperTools,
	usersResourceRestPath,
} ) {
	const { originalOptions, fetchingOptions } = useContext( Options );
	const { plugin_configured: pluginConfigured } = originalOptions;
	const [ fetchingUser, setFetchingUser ] = useState( false );
	const [ developerToolsOption, setDeveloperToolsOption ] = useState( null );
	const [ reviewPanelDismissedForTemplateMode, setReviewPanelDismissedForTemplateMode ] = useState( null );
	const [ originalDeveloperToolsOption, setOriginalDeveloperToolsOption ] = useState( null );
	const [ savingDeveloperToolsOption, setSavingDeveloperToolsOption ] = useState( false );
	const [ savingReviewPanelDismissedForTemplateMode, setSavingReviewPanelDismissedForTemplateMode ] = useState( false );
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

		if ( ! pluginConfigured && onlyFetchIfPluginIsConfigured ) {
			setOriginalDeveloperToolsOption( null );
			setDeveloperToolsOption( null );
			return;
		}

		if ( ! usersResourceRestPath || fetchingUser || null !== originalDeveloperToolsOption ) {
			return;
		}

		/**
		 * Fetches user data from the REST endpoint for the current user.
		 */
		( async () => {
			setFetchingUser( true );

			try {
				const fetchedUser = await apiFetch( {
					path: `${ usersResourceRestPath }/me`,
				} );

				if ( true === hasUnmounted.current ) {
					return;
				}

				setOriginalDeveloperToolsOption( fetchedUser[ userOptionDeveloperTools ] );
				setDeveloperToolsOption( fetchedUser[ userOptionDeveloperTools ] );
				setReviewPanelDismissedForTemplateMode( fetchedUser[ userFieldReviewPanelDismissedForTemplateMode ] );
			} catch ( e ) {
				setAsyncError( e );
				return;
			}

			setFetchingUser( false );
		} )();
	}, [ onlyFetchIfPluginIsConfigured, fetchingOptions, fetchingUser, originalDeveloperToolsOption, pluginConfigured, setAsyncError, userFieldReviewPanelDismissedForTemplateMode, userOptionDeveloperTools, usersResourceRestPath ] );

	/**
	 * Sends the dev tools option back to the REST endpoint to be saved.
	 */
	const saveDeveloperToolsOption = useCallback( async () => {
		if ( ! hasDeveloperToolsOptionChange ) {
			return;
		}

		setSavingDeveloperToolsOption( true );

		try {
			const fetchedUser = await apiFetch( {
				method: 'post',
				path: `${ usersResourceRestPath }/me`,
				data: {
					[ userOptionDeveloperTools ]: developerToolsOption,
				},
			} );

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
	}, [ hasDeveloperToolsOptionChange, developerToolsOption, setAsyncError, userOptionDeveloperTools, usersResourceRestPath ] );

	/**
	 * Sends the template mode for which the "Review" panel is dismissed back to
	 * the REST endpoint to be saved.
	 */
	const saveReviewPanelDismissedForTemplateMode = useCallback( async ( templateMode ) => {
		if ( savingReviewPanelDismissedForTemplateMode ) {
			return;
		}

		// Update the local state immediately.
		setReviewPanelDismissedForTemplateMode( templateMode );
		setSavingReviewPanelDismissedForTemplateMode( true );

		try {
			await apiFetch( {
				method: 'post',
				path: `${ usersResourceRestPath }/me`,
				data: {
					[ userFieldReviewPanelDismissedForTemplateMode ]: templateMode,
				},
			} );

			if ( true === hasUnmounted.current ) {
				return;
			}
		} catch ( e ) {
			setAsyncError( e );
			return;
		}

		setSavingReviewPanelDismissedForTemplateMode( false );
	}, [ savingReviewPanelDismissedForTemplateMode, setAsyncError, userFieldReviewPanelDismissedForTemplateMode, usersResourceRestPath ] );

	return (
		<User.Provider
			value={
				{
					developerToolsOption,
					fetchingUser,
					didSaveDeveloperToolsOption,
					hasDeveloperToolsOptionChange,
					reviewPanelDismissedForTemplateMode,
					originalDeveloperToolsOption,
					saveDeveloperToolsOption,
					savingDeveloperToolsOption,
					setDeveloperToolsOption,
					saveReviewPanelDismissedForTemplateMode,
					savingReviewPanelDismissedForTemplateMode,
				}
			}
		>
			{ children }
		</User.Provider>
	);
}

UserContextProvider.propTypes = {
	children: PropTypes.any,
	onlyFetchIfPluginIsConfigured: PropTypes.bool,
	userFieldReviewPanelDismissedForTemplateMode: PropTypes.string.isRequired,
	userOptionDeveloperTools: PropTypes.string.isRequired,
	usersResourceRestPath: PropTypes.string.isRequired,
};
