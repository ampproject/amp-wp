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
	const { hasSavedOptions, saveOptions, saveOptionsError, savingOptions } = useContext( Options );
	const { hasSavedUserOptions, saveUserOptions, saveUserOptionsError, savingUserOptions } = useContext( User );

	/**
	 * Triggers saving of options on arrival of this screen.
	 *
	 * @todo Possibly wait for a different user action to save.
	 */
	useEffect( () => {
		if ( ! hasSavedOptions && ! savingOptions && ! saveOptionsError ) {
			saveOptions();
		}
	}, [ hasSavedOptions, saveOptions, savingOptions, saveOptionsError ] );

	/**
	 * Triggers saving of user options on arrival of this screen.
	 *
	 * @todo Possibly wait for a different user action to save.
	 */
	useEffect( () => {
		if ( ! hasSavedUserOptions && ! savingUserOptions && ! saveUserOptionsError ) {
			saveUserOptions();
		}
	}, [ hasSavedUserOptions, savingUserOptions, saveUserOptions, saveUserOptionsError ] );

	if ( saveOptionsError || saveUserOptionsError ) {
		return (
			<p>
				{ __( 'There was an error saving options.', 'amp' ) }
			</p>
		);
	}

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
