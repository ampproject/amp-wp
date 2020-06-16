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
import { User1, User2 } from '../../components/svg/user-icons';
import { Loading } from '../../components/loading';
import { Selectable } from '../../components/selectable';
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
		if ( canGoForward === false ) {
			setCanGoForward( true );
		}
	}, [ canGoForward, developerToolsOption, setCanGoForward ] );

	const disableInputID = 'technical-background-disable';
	const enableInputID = 'technical-background-enable';

	if ( fetchingUser ) {
		return <Loading />;
	}

	return (
		<div>
			<p>
				{ __( 'In order to recommend the best AMP experience for your site, we\'d like to know if you\'re a more technical user', 'amp' ) }
			</p>
			<form>
				<Selectable className={ `technical-background-option-container` } selected={ true === developerToolsOption }>
					<label htmlFor={ enableInputID } className="technical-background-option">
						<div className="technical-background-option__input-container">
							<input
								type="radio"
								id={ enableInputID }
								checked={ true === developerToolsOption }
								onChange={ () => {
									setDeveloperToolsOption( true );
								} }
							/>
						</div>
						<User1 />
						<div className="technical-background-option__description">
							<h2>
								{ __( 'Developer or technically savvy', 'amp' ) }
							</h2>
							<p>
								{ __( 'I am a “Developer or technically savvy” user. I can do WordPress development such as making changes to themes and plugins. I have some familiarity with HTML, CSS, JavaScript, and PHP. I am technically savvy enough to build full WordPress sites out of plugins and themes and can address configuration issues and understand', 'amp' ) }
							</p>
						</div>
					</label>
				</Selectable>

				<Selectable className={ `technical-background-option-container` } selected={ false === developerToolsOption }>
					<label htmlFor={ disableInputID } className="technical-background-option">
						<div className="technical-background-option__input-container">
							<input
								type="radio"
								id={ disableInputID }
								checked={ false === developerToolsOption }
								onChange={ () => {
									setDeveloperToolsOption( false );
								} }
							/>
						</div>
						<User2 />
						<div className="technical-background-option__description">
							<h2>
								{ __( 'Non-technically savvy or wanting a simpler setup', 'amp' ) }
							</h2>
							<p>
								{ __( 'I am not a developer and I am not responsible for configuring and fixing issues on my site. I am a site owner and/or content creator who wants to take advantage of AMP performance.', 'amp' ) }
							</p>
						</div>
					</label>
				</Selectable>
			</form>
		</div>
	);
}
