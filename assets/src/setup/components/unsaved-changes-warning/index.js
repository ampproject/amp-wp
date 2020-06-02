/**
 * WordPress dependencies
 */
import { useEffect, useContext, useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { __ } from '@wordpress/i18n';
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
			event.returnValue = __( 'This page has unsaved changes. Are you sure you want to leave?', 'amp' );
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
