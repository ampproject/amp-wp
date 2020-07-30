/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext, useEffect, useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Navigation } from '../../components/navigation-context-provider';
import { User } from '../../components/user-context-provider';
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
				<svg width="130" height="136" viewBox="0 0 130 136" fill="none" xmlns="http://www.w3.org/2000/svg">
					<g clipPath="url(#clip-technical-background)">
						<path d="M84.847 103.785H30.847C28.047 103.785 25.847 101.585 25.847 98.7852V45.7852C25.847 42.9852 28.047 40.7852 30.847 40.7852H89.847C92.647 40.7852 94.847 42.9852 94.847 45.7852V93.7852C94.847 99.2852 90.347 103.785 84.847 103.785Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M73.847 89.1867H46.847C44.047 89.1867 41.847 86.9867 41.847 84.1867V60.3867C41.847 57.5867 44.047 55.3867 46.847 55.3867H73.847C76.647 55.3867 78.847 57.5867 78.847 60.3867V84.1867C78.847 86.9867 76.647 89.1867 73.847 89.1867Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M60.847 40.7852V19.7852" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M37.847 40.7852V11.7852" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M26.247 72.7852H7.84698" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M62.847 104.086V126.786" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M84.847 103.785V128.785" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M95.147 76.7852H107.847" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M94.847 51.7852H116.847" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M82.847 40.3852V29.7852" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M37.847 104.086V119.786" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M37.847 129.785C40.6084 129.785 42.847 127.547 42.847 124.785C42.847 122.024 40.6084 119.785 37.847 119.785C35.0856 119.785 32.847 122.024 32.847 124.785C32.847 127.547 35.0856 129.785 37.847 129.785Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M6.84698 110.785C9.60841 110.785 11.847 108.547 11.847 105.785C11.847 103.024 9.60841 100.785 6.84698 100.785C4.08556 100.785 1.84698 103.024 1.84698 105.785C1.84698 108.547 4.08556 110.785 6.84698 110.785Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M37.847 11.7852C40.6084 11.7852 42.847 9.54658 42.847 6.78516C42.847 4.02373 40.6084 1.78516 37.847 1.78516C35.0856 1.78516 32.847 4.02373 32.847 6.78516C32.847 9.54658 35.0856 11.7852 37.847 11.7852Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M82.847 29.7852C85.6084 29.7852 87.847 27.5466 87.847 24.7852C87.847 22.0237 85.6084 19.7852 82.847 19.7852C80.0856 19.7852 77.847 22.0237 77.847 24.7852C77.847 27.5466 80.0856 29.7852 82.847 29.7852Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M123.847 81.7852C126.608 81.7852 128.847 79.5466 128.847 76.7852C128.847 74.0237 126.608 71.7852 123.847 71.7852C121.086 71.7852 118.847 74.0237 118.847 76.7852C118.847 79.5466 121.086 81.7852 123.847 81.7852Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M121.847 56.7852C124.608 56.7852 126.847 54.5466 126.847 51.7852C126.847 49.0237 124.608 46.7852 121.847 46.7852C119.086 46.7852 116.847 49.0237 116.847 51.7852C116.847 54.5466 119.086 56.7852 121.847 56.7852Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M60.847 19.7852C62.5038 19.7852 63.847 18.442 63.847 16.7852C63.847 15.1283 62.5038 13.7852 60.847 13.7852C59.1901 13.7852 57.847 15.1283 57.847 16.7852C57.847 18.442 59.1901 19.7852 60.847 19.7852Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M84.847 134.785C86.5038 134.785 87.847 133.442 87.847 131.785C87.847 130.128 86.5038 128.785 84.847 128.785C83.1901 128.785 81.847 130.128 81.847 131.785C81.847 133.442 83.1901 134.785 84.847 134.785Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M110.847 79.7852C112.504 79.7852 113.847 78.442 113.847 76.7852C113.847 75.1283 112.504 73.7852 110.847 73.7852C109.19 73.7852 107.847 75.1283 107.847 76.7852C107.847 78.442 109.19 79.7852 110.847 79.7852Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M86.847 50.2852C87.9516 50.2852 88.847 49.3897 88.847 48.2852C88.847 47.1806 87.9516 46.2852 86.847 46.2852C85.7424 46.2852 84.847 47.1806 84.847 48.2852C84.847 49.3897 85.7424 50.2852 86.847 50.2852Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M33.847 50.2852C34.9516 50.2852 35.847 49.3897 35.847 48.2852C35.847 47.1806 34.9516 46.2852 33.847 46.2852C32.7424 46.2852 31.847 47.1806 31.847 48.2852C31.847 49.3897 32.7424 50.2852 33.847 50.2852Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M86.847 98.2852C87.9516 98.2852 88.847 97.3897 88.847 96.2852C88.847 95.1806 87.9516 94.2852 86.847 94.2852C85.7424 94.2852 84.847 95.1806 84.847 96.2852C84.847 97.3897 85.7424 98.2852 86.847 98.2852Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M33.847 98.2852C34.9516 98.2852 35.847 97.3897 35.847 96.2852C35.847 95.1806 34.9516 94.2852 33.847 94.2852C32.7424 94.2852 31.847 95.1806 31.847 96.2852C31.847 97.3897 32.7424 98.2852 33.847 98.2852Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M4.84698 75.7852C6.50384 75.7852 7.84698 74.442 7.84698 72.7852C7.84698 71.1283 6.50384 69.7852 4.84698 69.7852C3.19013 69.7852 1.84698 71.1283 1.84698 72.7852C1.84698 74.442 3.19013 75.7852 4.84698 75.7852Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M62.847 132.785C64.5038 132.785 65.847 131.442 65.847 129.785C65.847 128.128 64.5038 126.785 62.847 126.785C61.1901 126.785 59.847 128.128 59.847 129.785C59.847 131.442 61.1901 132.785 62.847 132.785Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
						<path d="M25.547 86.5859H13.847L6.74701 93.6859V100.686" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
					</g>
					<defs>
						<clipPath id="clip-technical-background">
							<rect width="129" height="135" fill="white" transform="translate(0.846985 0.785156)" />
						</clipPath>
					</defs>
				</svg>
				<h1>
					{ __( 'Are you technical?', 'amp' ) }
				</h1>
				<p>
					{ __( 'In order to recommend the best plugin configuration options for your site, please indicate your level of technical expertise.', 'amp' ) }
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
							<div>
								<h2>
									{ __( 'Developer or technically savvy', 'amp' ) }
								</h2>
							</div>
							<p>
								{ __( 'I am a “Developer or technically savvy” user. I am familiar with HTML, CSS, JavaScript, and PHP. I am able to do WordPress development, including making changes to themes and plugins, as well as assembling full WordPress sites out of plugins and theme components. I can understand and address AMP validation issues.', 'amp' ) }
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
							<div>
								<h2>
									{ __( 'Non-technically savvy or wanting a simpler setup', 'amp' ) }
								</h2>
							</div>
							<p>
								{ __( 'I am not a developer and I am not responsible for configuring and fixing issues on my site. I am a site owner and/or content creator who wants to take advantage of AMP to build user-first sites.', 'amp' ) }
							</p>
						</div>
					</label>
				</Selectable>
			</form>
		</div>
	);
}
