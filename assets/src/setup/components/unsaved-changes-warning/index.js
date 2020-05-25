/**
 * WordPress dependencies
 */
import { useEffect, useContext, useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Options } from '../options-context-provider';

export function WizardUnsavedChangesWarning() {
	const { hasChanges, hasSaved } = useContext( Options );

	/**
	 * Warns the user if there are unsaved changes before leaving the wizard.
	 *
	 * @param {Event} event `beforeunload` event.
	 */
	const warnIfUnsavedChanges = useCallback( ( event ) => {
		if ( hasChanges && ! hasSaved ) {
			event.returnValue = '';
		}

		return null;
	}, [ hasChanges, hasSaved ] );

	useEffect( () => {
		window.addEventListener( 'beforeunload', warnIfUnsavedChanges );

		return () => {
			window.removeEventListener( 'beforeunload', warnIfUnsavedChanges );
		};
	}, [ warnIfUnsavedChanges ] );

	return null;
}
