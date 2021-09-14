/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext } from '@wordpress/element';
/**
 * Internal dependencies
 */
import { Desktop } from '../../components/desktop';
import { Phone } from '../../../components/phone';
import { Reader as ReaderIllustration } from '../../../components/svg/reader';
import { AMPInfo } from '../../../components/amp-info';
import { IconDesktop } from '../../../components/svg/icon-desktop';
import { IconMobile } from '../../../components/svg/icon-mobile';
import { Options } from '../../../components/options-context-provider';
import { ReaderThemes } from '../../../components/reader-themes-context-provider';
import DesktopIcon from '../../../components/svg/desktop-icon.svg';
import MobileIcon from '../../../components/svg/mobile-icon.svg';
import { SummaryHeader } from './summary-header';

/**
 * Summary screen when reader mode was selected.
 *
 * @param {Object} props
 * @param {Object} props.currentTheme Data for the theme currently active on the site.
 */
export function Reader( { currentTheme } ) {
	const { editedOptions } = useContext( Options );
	const { themes } = useContext( ReaderThemes );

	const { reader_theme: readerTheme, mobile_redirect: mobileRedirect } = editedOptions;

	const readerThemeData = themes ? themes.find( ( theme ) => theme.slug === readerTheme ) : {};

	const mobileCaption = mobileRedirect ? __( 'Mobile and AMP visitors', 'amp' ) : __( 'AMP visitors', 'amp' );
	const desktopCaption = mobileRedirect ? __( 'Desktop visitors', 'amp' ) : __( 'Desktop and non-AMP mobile visitors', 'amp' );

	return (
		<>
			<SummaryHeader
				illustration={ <ReaderIllustration /> }
				title={ __( 'Reader', 'amp' ) }
				text={ __( 'In Reader mode your site will have a non-AMP and an AMP version, and each version will use its own theme. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' ) }
			/>

			<div className="reader-summary__screens grid grid-2-1">
				<div>
					<div className="selectable selectable--bottom">
						<AMPInfo icon={ ( props ) => <IconDesktop { ...props } /> }>
							{ desktopCaption }
						</AMPInfo>

						<Desktop>
							{
								currentTheme.screenshot ? (
									<img
										src={ currentTheme.screenshot }
										alt={ currentTheme.name }
										loading="lazy"
										decoding="async"
										height="900"
										width="1200"
									/>
								) : <DesktopIcon />
							}

						</Desktop>

						<h3>
							{ currentTheme.name }
						</h3>
						<p className="amp-description">
							{ currentTheme.description }
						</p>
						{ currentTheme.url && currentTheme.url.length && (
							<a href={ currentTheme.url }>
								{ __( 'Learn more' ) }
							</a>
						) }
					</div>
				</div>
				<div>

					<div className="selectable selectable--bottom">
						<AMPInfo icon={ ( props ) => <IconMobile { ...props } /> }>
							{ mobileCaption }
						</AMPInfo>

						<Phone>
							{
								readerThemeData.screenshot_url ? (
									<img
										src={ readerThemeData.screenshot_url }
										alt={ readerThemeData.name }
										loading="lazy"
										decoding="async"
										height="2165"
										width="1000"
									/>
								) : <MobileIcon style={ { width: '100%' } } />
							}
						</Phone>

						<h3>
							{ readerThemeData.name }
						</h3>
						<p className="amp-description">
							{ readerThemeData.description }
						</p>
						{ readerThemeData.homepage && readerThemeData.homepage.length && (
							<a href={ readerThemeData.homepage } target="_blank" rel="noreferrer">
								{ __( 'Learn more' ) }
							</a>
						) }
					</div>

				</div>
			</div>
		</>
	);
}

Reader.propTypes = {
	currentTheme: PropTypes.shape( {
		description: PropTypes.string,
		name: PropTypes.string,
		screenshot: PropTypes.string,
		url: PropTypes.string,
	} ).isRequired,
};
