/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Navigation } from '../../components/navigation-context-provider';
import { User } from '../../components/user-context-provider';

/**
 * Screen for selecting the user's technical background.
 */
export function TechnicalBackground() {
	const { canGoForward, setCanGoForward } = useContext( Navigation );
	const {
		developerToolsOption,
		fetchingUser,
		setDeveloperToolsOption,
	} = useContext( User );

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( canGoForward === false ) {
			setCanGoForward( true );
		}
	}, [ canGoForward, setCanGoForward ] );

	const disableInputID = 'amp-technical-background-disable';
	const enableInputID = 'amp-technical-background-enable';

	return (
		<div>
			<h1>
				{ __( 'Technical background', 'amp' ) }
			</h1>
			<p>
				{ __( 'In order to recommend the best AMP experience for your site, we\'d like to know if you\'re a more technical user', 'amp' ) }
			</p>
			<form>

				<label htmlFor={ disableInputID }>

					<input
						type="radio"
						id={ disableInputID }
						checked={ 'enabled' === developerToolsOption }
						onChange={ () => {
							setDeveloperToolsOption( 'enabled' );
						} }
					/>
				</label>

				<label htmlFor={ enableInputID }>

					<input
						type="radio"
						id={ enableInputID }
						checked={ 'disabled' === developerToolsOption }
						onChange={ () => {
							setDeveloperToolsOption( 'disabled' );
						} }
					/>
				</label>
			</form>
		</div>
	);
}
