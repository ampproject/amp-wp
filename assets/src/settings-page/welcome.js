/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Options } from '../components/options-context-provider';
import { ReaderThemes } from '../components/reader-themes-context-provider';

/**
 * Welcome component on the settings screen.
 */
export function Welcome() {
	const { editedOptions } = useContext( Options );
	const { templateModeWasOverridden } = useContext( ReaderThemes );

	const {
		customizer_link: customizerLink,
		onboarding_wizard_link: onboardingWizardLink,
		plugin_configured: pluginConfigured,
	} = editedOptions;

	return (
		<div className="settings-welcome">
			<div className="selectable selectable--left">
				<div className="settings-welcome__illustration">
					<svg width="62" height="51" viewBox="0 0 62 51" fill="none" xmlns="http://www.w3.org/2000/svg">
						<g clipPath="url(#welcome-svg-clip)">
							<path d="M19.0226 3.89844H39.5226C45.0226 3.89844 49.4226 8.29844 49.4226 13.7984V34.2984C49.4226 39.7984 45.0226 44.1984 39.5226 44.1984H19.0226C13.5226 44.1984 9.12256 39.7984 9.12256 34.2984V13.7984C9.12256 8.29844 13.5226 3.89844 19.0226 3.89844Z" fill="white" stroke="#2459E7" strokeWidth="2" />
							<path d="M17.8227 11.1992C18.7227 11.1992 19.4227 11.8992 19.4227 12.7992V35.6992C19.4227 36.5992 18.7227 37.2992 17.8227 37.2992C16.9227 37.2992 16.2227 36.5992 16.2227 35.6992V12.6992C16.2227 11.7992 16.9227 11.1992 17.8227 11.1992Z" fill="white" stroke="#2459E7" strokeWidth="2" />
							<path d="M17.8228 21.9C19.5901 21.9 21.0228 20.4673 21.0228 18.7C21.0228 16.9327 19.5901 15.5 17.8228 15.5C16.0555 15.5 14.6228 16.9327 14.6228 18.7C14.6228 20.4673 16.0555 21.9 17.8228 21.9Z" fill="white" stroke="#2459E7" strokeWidth="2" />
							<path d="M29.3227 37.0977C28.4227 37.0977 27.7227 36.3977 27.7227 35.4977V12.6977C27.7227 11.7977 28.4227 11.0977 29.3227 11.0977C30.2227 11.0977 30.9227 11.7977 30.9227 12.6977V35.5977C30.9227 36.3977 30.2227 37.0977 29.3227 37.0977Z" fill="white" stroke="#2459E7" strokeWidth="2" />
							<path d="M40.9225 37.0977C40.0225 37.0977 39.3225 36.3977 39.3225 35.4977V12.6977C39.3225 11.7977 40.0225 11.0977 40.9225 11.0977C41.8225 11.0977 42.5225 11.7977 42.5225 12.6977V35.5977C42.5225 36.3977 41.8225 37.0977 40.9225 37.0977Z" fill="white" stroke="#2459E7" strokeWidth="2" />
							<path d="M40.9227 24.0992C42.69 24.0992 44.1227 22.6665 44.1227 20.8992C44.1227 19.1319 42.69 17.6992 40.9227 17.6992C39.1553 17.6992 37.7227 19.1319 37.7227 20.8992C37.7227 22.6665 39.1553 24.0992 40.9227 24.0992Z" fill="white" stroke="#2459E7" strokeWidth="2" />
							<path d="M29.2227 30.9977C30.99 30.9977 32.4227 29.565 32.4227 27.7977C32.4227 26.0303 30.99 24.5977 29.2227 24.5977C27.4554 24.5977 26.0227 26.0303 26.0227 27.7977C26.0227 29.565 27.4554 30.9977 29.2227 30.9977Z" fill="white" stroke="#2459E7" strokeWidth="2" />
							<path d="M47.3225 5.19784C47.9225 3.69784 49.9225 0.797843 53.4225 1.49784" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
							<path d="M50.5227 7.19675C51.7227 6.69675 54.5227 6.29675 56.2227 9.09675" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
							<path d="M12.4225 44.7969C11.9225 45.7969 10.9225 48.1969 11.1225 49.3969" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
							<path d="M8.92266 43.6992C8.42266 44.0992 7.52266 44.6992 6.72266 45.1992" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
							<path d="M7.42261 39.8984C5.92261 40.4984 2.82261 41.5984 1.92261 41.7984" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
							<path d="M3.92251 48.8992C4.80617 48.8992 5.52251 48.1829 5.52251 47.2992C5.52251 46.4156 4.80617 45.6992 3.92251 45.6992C3.03885 45.6992 2.32251 46.4156 2.32251 47.2992C2.32251 48.1829 3.03885 48.8992 3.92251 48.8992Z" fill="#2459E7" />
							<path d="M60.1227 12.7C61.0064 12.7 61.7227 11.9837 61.7227 11.1C61.7227 10.2163 61.0064 9.5 60.1227 9.5C59.2391 9.5 58.5227 10.2163 58.5227 11.1C58.5227 11.9837 59.2391 12.7 60.1227 12.7Z" fill="#2459E7" />
						</g>
						<defs>
							<clipPath id="welcome-svg-clip">
								<rect width="60.8" height="50" fill="white" transform="translate(0.922607 0.398438)" />
							</clipPath>
						</defs>
					</svg>

				</div>

				<div className="settings-welcome__body">
					<h2>
						{ pluginConfigured
							? (
								<>
									{ __( 'AMP Settings Configured', 'amp' ) }

									<svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
										<mask id="check-circle-mask" mask-type="alpha" maskUnits="userSpaceOnUse" x="2" y="2" width="21" height="21">
											<path fillRule="evenodd" clipRule="evenodd" d="M12.7537 2.60938C7.23366 2.60938 2.75366 7.08938 2.75366 12.6094C2.75366 18.1294 7.23366 22.6094 12.7537 22.6094C18.2737 22.6094 22.7537 18.1294 22.7537 12.6094C22.7537 7.08938 18.2737 2.60938 12.7537 2.60938ZM12.7537 20.6094C8.34366 20.6094 4.75366 17.0194 4.75366 12.6094C4.75366 8.19938 8.34366 4.60938 12.7537 4.60938C17.1637 4.60938 20.7537 8.19938 20.7537 12.6094C20.7537 17.0194 17.1637 20.6094 12.7537 20.6094ZM10.7537 14.7794L17.3437 8.18937L18.7537 9.60938L10.7537 17.6094L6.75366 13.6094L8.16366 12.1994L10.7537 14.7794Z" fill="white" />
										</mask>
										<g mask="url(#check-circle-mask)">
											<rect x="0.753662" y="0.609375" width="24" height="24" fill="#2459E7" />
										</g>
									</svg>
								</>
							)
							: __( 'Configure AMP', 'amp' )
						}

					</h2>
					<p>
						{ __( 'The AMP configuration wizard helps you choose the best configuration settings for your site.', 'amp' ) }
					</p>

					<a className="components-button is-primary settings-welcome__button" href={ onboardingWizardLink } >
						{ pluginConfigured ? __( 'Reopen Wizard', 'amp' ) : __( 'Open Wizard', 'amp' ) }
					</a>

					{
						customizerLink && templateModeWasOverridden === false && (
							<a className="components-button is-secondary" href={ customizerLink } rel="noreferrer">
								{ __( 'Customize Reader Theme', 'amp' ) }
							</a>
						)
					}
				</div>
			</div>
		</div>
	);
}
