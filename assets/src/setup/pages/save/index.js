/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Options } from '../../components/options-context-provider';
import { Loading } from '../../components/loading';
import { User } from '../../components/user-context-provider';

/**
 * Final screen, where data is saved.
 */
export function Save() {
	const { hasSavedOptions, saveOptions, savingOptions } = useContext( Options );
	const { hasSavedUserOptions, saveUserOptions, savingUserOptions } = useContext( User );

	/**
	 * Triggers saving of options on arrival of this screen.
	 *
	 * @todo Possibly wait for a different user action to save.
	 */
	useEffect( () => {
		if ( ! hasSavedOptions && ! savingOptions ) {
			saveOptions();
		}
	}, [ hasSavedOptions, saveOptions, savingOptions ] );

	/**
	 * Triggers saving of user options on arrival of this screen.
	 *
	 * @todo Possibly wait for a different user action to save.
	 */
	useEffect( () => {
		if ( ! hasSavedUserOptions && ! savingUserOptions ) {
			saveUserOptions();
		}
	}, [ hasSavedUserOptions, savingUserOptions, saveUserOptions ] );

	if ( savingOptions || savingUserOptions ) {
		return <Loading />;
	}

	if ( ! hasSavedOptions || ! hasSavedUserOptions ) {
		return null;
	}

	return (
		<div>
			{ __( 'Options saved', 'amp' ) }
		</div>
	);
}
