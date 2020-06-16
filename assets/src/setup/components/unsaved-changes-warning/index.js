/**
 * WordPress dependencies
 */
import { useEffect, useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { __ } from '@wordpress/i18n';
import { Options } from '../options-context-provider';
import { User } from '../user-context-provider';

/**
 * If there are unsaved changes in the wizard, warns the user before exiting the page.
 *
 * @return {null} Renders nothing.
 */
export function WizardUnsavedChangesWarning() {
	const { hasOptionsChanges, didSaveOptions } = useContext( Options );
	const { hasUserOptionsChanges, didSaveUserOptions } = useContext( User );

	useEffect( () => {
		if ( ( hasOptionsChanges && ! didSaveOptions ) || ( hasUserOptionsChanges && ! didSaveUserOptions ) ) {
			const warnIfUnsavedChanges = ( event ) => {
				event.returnValue = __( 'This page has unsaved changes. Are you sure you want to leave?', 'amp' );

				return null;
			};

			window.addEventListener( 'beforeunload', warnIfUnsavedChanges );

			return () => {
				window.removeEventListener( 'beforeunload', warnIfUnsavedChanges );
			};
		}

		return () => undefined;
	}, [ hasOptionsChanges, didSaveOptions, hasUserOptionsChanges, didSaveUserOptions ] );

	return null;
}
