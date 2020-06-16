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
	const { didSaveOptions, saveOptions, savingOptions } = useContext( Options );
	const { didSaveUserOptions, saveUserOptions, savingUserOptions } = useContext( User );

	/**
	 * Triggers saving of options on arrival of this screen.
	 *
	 * @todo Possibly wait for a different user action to save.
	 */
	useEffect( () => {
		if ( ! didSaveOptions && ! savingOptions ) {
			saveOptions();
		}
	}, [ didSaveOptions, saveOptions, savingOptions ] );

	/**
	 * Triggers saving of user options on arrival of this screen.
	 *
	 * @todo Possibly wait for a different user action to save.
	 */
	useEffect( () => {
		if ( ! didSaveUserOptions && ! savingUserOptions ) {
			saveUserOptions();
		}
	}, [ didSaveUserOptions, savingUserOptions, saveUserOptions ] );

	if ( savingOptions || savingUserOptions ) {
		return <Loading />;
	}

	if ( ! didSaveOptions || ! didSaveUserOptions ) {
		return null;
	}

	return (
		<div>
			{ __( 'Options saved', 'amp' ) }
		</div>
	);
}
