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

/**
 * Final screen, where data is saved.
 */
export function Save() {
	const { hasSaved, saveOptions, savingOptions } = useContext( Options );

	/**
	 * Triggers saving of options on arrival of this screen.
	 *
	 * @todo Possibly wait for a different user action to save.
	 */
	useEffect( () => {
		if ( ! hasSaved && ! savingOptions ) {
			saveOptions();
		}
	}, [ hasSaved, saveOptions, savingOptions ] );

	if ( savingOptions ) {
		return <Loading />;
	}

	if ( ! hasSaved ) {
		return null;
	}

	return (
		<div>
			{ __( 'Options saved', 'amp' ) }
		</div>
	);
}
