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
import { User1, User2 } from '../../components/svg/users';
import { Loading } from '../../components/loading';
import './style.css';

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
		if ( canGoForward === false && developerToolsOption && 'unset' !== developerToolsOption ) {
			setCanGoForward( true );
		}
	}, [ canGoForward, developerToolsOption, setCanGoForward ] );

	const disableInputID = 'amp-technical-background-disable';
	const enableInputID = 'amp-technical-background-enable';

	if ( fetchingUser ) {
		return <Loading />;
	}

	return (
		<div>
			<p>
				{ __( 'In order to recommend the best AMP experience for your site, we\'d like to know if you\'re a more technical user', 'amp' ) }
			</p>
			<form>
				<div className={ `amp-technical-background-option-container ${ 'enabled' === developerToolsOption ? 'amp-technical-background-option-container--active' : '' }` }>
					<label htmlFor={ disableInputID } className="amp-technical-background-option">
						<div className="amp-technical-background-option__input-container">
							<input
								type="radio"
								id={ disableInputID }
								checked={ 'enabled' === developerToolsOption }
								onChange={ () => {
									setDeveloperToolsOption( 'enabled' );
								} }
							/>
						</div>
						<User1 />
						<div className="amp-technical-background-option__description">
							<h2>
								{ __( 'Developer or Technically Savvy', 'amp' ) }
							</h2>
							<p>
								{ __( 'I am a “Developer or Technically Savvy” user. I can do WordPress development such as making changes to themes and plugins. I have some familiarity with HTML, CSS, JavaScript, and PHP. I am technically savvy enough to build full WordPress sites out of plugins and themes and can address configuration issues and understand', 'amp' ) }
							</p>
						</div>
					</label>
				</div>

				<div className={ `amp-technical-background-option-container ${ 'disabled' === developerToolsOption ? 'amp-technical-background-option-container--active' : '' }` }>
					<label htmlFor={ enableInputID } className="amp-technical-background-option">
						<div className="amp-technical-background-option__input-container">
							<input
								type="radio"
								id={ enableInputID }
								checked={ 'disabled' === developerToolsOption }
								onChange={ () => {
									setDeveloperToolsOption( 'disabled' );
								} }
							/>
						</div>
						<User2 />
						<div className="amp-technical-background-option__description">
							<h2>
								{ __( 'Non-technically Savvy or Wanting a simpler setup', 'amp' ) }
							</h2>
							<p>
								{ __( 'I am not a developer and I am not responsible for configuring and fixing issues on my site. I am a site owner and/or content creator who wants to take advantage of AMP performance.', 'amp' ) }
							</p>
						</div>
					</label>
				</div>
			</form>
		</div>
	);
}
