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
	const { didSaveDeveloperToolsOption, saveDeveloperToolsOption, savingDeveloperToolsOption } = useContext( User );

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
		if ( ! didSaveDeveloperToolsOption && ! savingDeveloperToolsOption ) {
			saveDeveloperToolsOption();
		}
	}, [ didSaveDeveloperToolsOption, savingDeveloperToolsOption, saveDeveloperToolsOption ] );

	if ( savingOptions || savingDeveloperToolsOption ) {
		return <Loading />;
	}

	if ( ! didSaveOptions || ! didSaveDeveloperToolsOption ) {
		return null;
	}

	return (
		<div>
			{ __( 'Options saved', 'amp' ) }
		</div>
	);
}
