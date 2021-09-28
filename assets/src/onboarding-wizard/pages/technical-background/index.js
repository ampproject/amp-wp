/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext, useEffect, useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Navigation } from '../../components/navigation-context-provider';
import { User } from '../../../components/user-context-provider';
import { User1, User2 } from '../../../components/svg/user-icons';
import { Selectable } from '../../../components/selectable';
import './style.css';
import { Loading } from '../../../components/loading';

/**
 * Screen for selecting the user's technical background.
 */
export function TechnicalBackground() {
	const { setCanGoForward } = useContext( Navigation );
	const {
		developerToolsOption,
		fetchingUser,
		setDeveloperToolsOption,
	} = useContext( User );

	const onChange = useCallback( ( newValue ) => {
		setDeveloperToolsOption( newValue );
	}, [ setDeveloperToolsOption ] );

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( 'boolean' === typeof developerToolsOption ) {
			setCanGoForward( true );
		}
	}, [ developerToolsOption, setCanGoForward ] );

	const disableInputID = 'technical-background-disable';
	const enableInputID = 'technical-background-enable';

	if ( fetchingUser ) {
		return <Loading />;
	}

	return (
		<div className="technical-background">
			<div className="technical-background__header">
				<h1>
					{ __( 'Technical Background', 'amp' ) }
				</h1>
				<p>
					{ __( 'To recommend the best AMP experience we’d like to know if you’re a more technical user, or less technical.', 'amp' ) }
				</p>
			</div>
			<form>
				<Selectable className={ `technical-background-option-container` } selected={ true === developerToolsOption }>
					<label htmlFor={ enableInputID } className="technical-background-option">
						<div className="technical-background-option__input-container">
							<input
								type="radio"
								id={ enableInputID }
								checked={ true === developerToolsOption }
								onChange={ () => {
									onChange( true );
								} }
							/>
						</div>
						<User1 />
						<div className="technical-background-option__description">
							<h2>
								{ __( 'Developer or technically savvy', 'amp' ) }
							</h2>
							<p>
								{ __( 'I can do WordPress development by modifying themes and plugins. I am familiar with PHP, JavaScript, HTML, and CSS.', 'amp' ) }
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
									onChange( false );
								} }
							/>
						</div>
						<User2 />
						<div className="technical-background-option__description">
							<h2>
								{ __( 'Non-technical or wanting a simpler setup', 'amp' ) }
							</h2>
							<p>
								{ __( 'I am not responsible for configuring and fixing issues on my site. I am a site owner and/or content creator who wants to take advantage of AMP performance.', 'amp' ) }
							</p>
						</div>
					</label>
				</Selectable>
			</form>
		</div>
	);
}
