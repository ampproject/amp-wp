/**
 * WordPress dependencies
 */
import { useEffect, useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { User } from '../../onboarding-wizard/components/user-context-provider';
import { Options } from '../options-context-provider';

/**
 * If there are unsaved changes in the wizard, warns the user before exiting the page.
 *
 * @return {null} Renders nothing.
 */
export function UnsavedChangesWarning() {
	const { hasOptionsChanges, didSaveOptions } = useContext( Options );
	const { hasDeveloperToolsOptionChange, didSaveDeveloperToolsOption } = useContext( User );

	useEffect( () => {
		if ( ( hasOptionsChanges && ! didSaveOptions ) || ( hasDeveloperToolsOptionChange && ! didSaveDeveloperToolsOption ) ) {
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
	}, [ hasOptionsChanges, didSaveOptions, hasDeveloperToolsOptionChange, didSaveDeveloperToolsOption ] );

	return null;
}
