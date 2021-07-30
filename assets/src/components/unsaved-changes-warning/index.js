/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useEffect, useContext, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { User } from '../../components/user-context-provider';
import { Options } from '../options-context-provider';

/**
 * If there are unsaved changes in the wizard, warns the user before exiting the page.
 *
 * @param {Object}  props                    Component props.
 * @param {boolean} props.excludeUserContext Whether to exclude listening to user context.
 * @param {Element} props.appRoot            React app root.
 * @return {null} Renders nothing.
 */
export function UnsavedChangesWarning( { excludeUserContext = false, appRoot } ) {
	const { hasOptionsChanges, didSaveOptions } = useContext( Options );
	const [ userState, setUserState ] = useState( { hasDeveloperToolsOptionChange: false, didSaveDeveloperToolsOption: true } );

	const { hasDeveloperToolsOptionChange, didSaveDeveloperToolsOption } = userState;

	useEffect( () => {
		if ( ( hasOptionsChanges && ! didSaveOptions ) || ( hasDeveloperToolsOptionChange && ! didSaveDeveloperToolsOption ) ) {
			const warnIfUnsavedChanges = ( event ) => {
				event.returnValue = __( 'This page has unsaved changes. Are you sure you want to leave?', 'amp' );

				return null;
			};

			appRoot.ownerDocument.addEventListener( 'beforeunload', warnIfUnsavedChanges );

			return () => {
				appRoot.ownerDocument.removeEventListener( 'beforeunload', warnIfUnsavedChanges );
			};
		}

		return () => undefined;
	}, [ appRoot, hasOptionsChanges, didSaveOptions, hasDeveloperToolsOptionChange, didSaveDeveloperToolsOption ] );

	return excludeUserContext ? null : <WithUserContext setUserState={ setUserState } />;
}
UnsavedChangesWarning.propTypes = {
	appRoot: PropTypes.instanceOf( global.Element ),
	excludeUserContext: PropTypes.bool,
};

/**
 * Sends user context back up to the parent component.
 *
 * @param {Object}   props
 * @param {Function} props.setUserState Sets updated user state.
 */
function WithUserContext( { setUserState } ) {
	const { hasDeveloperToolsOptionChange, didSaveDeveloperToolsOption } = useContext( User );

	useEffect( () => {
		setUserState( { hasDeveloperToolsOptionChange, didSaveDeveloperToolsOption } );
	}, [ hasDeveloperToolsOptionChange, didSaveDeveloperToolsOption, setUserState ] );

	return null;
}
