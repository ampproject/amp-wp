/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { ToggleControl } from '@wordpress/components';
import { useContext } from '@wordpress/element';
/**
 * Internal dependencies
 */
import { Options } from '../../components/options-context-provider';
import { Desktop } from '../../components/desktop';
import { Phone } from '../../components/phone';
import { ReaderThemes } from '../../components/reader-themes-context-provider';

/**
 * Summary screen when reader mode was selected.
 *
 * @param {Object} props
 * @param {Object} props.currentTheme Data for the theme currently active on the site.
 */
export function Reader( { currentTheme } ) {
	const { options, updateOptions } = useContext( Options );
	const { themes } = useContext( ReaderThemes );

	const { reader_theme: readerTheme, mobile_redirect: mobileRedirect } = options;

	const readerThemeData = themes ? themes.find( ( theme ) => theme.slug === readerTheme ) : {};

	return (
		<div className="reader-summary">
			<p className="reader-summary__description">
				{ __( 'In Reader mode your site will have a non-AMP and an AMP version, and each version will use its own theme. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' ) }
			</p>
			<ToggleControl
				label={ __( 'Redirect mobile visitors to AMP', 'amp' ) }
				help={ __( 'AMP is not only for mobile.', 'amp' ) }
				checked={ true === mobileRedirect }
				onChange={ () => {
					updateOptions( { mobile_redirect: ! mobileRedirect } );
				} }
			/>

			<div className="reader-summary__screens">
				<div>
					<div className="selectable selectable--bottom">
						<Desktop>
							<img
								src={ currentTheme.screenshot }
								alt={ currentTheme.name }
								loading="lazy"
								decoding="async"
								height="900"
								width="1200"
							/>

						</Desktop>

						<h3>
							{ currentTheme.name }
						</h3>
						<p>
							{ currentTheme.description }
						</p>
						{ currentTheme.url && currentTheme.url.length && (
							<a href={ currentTheme.url }>
								{ __( 'Learn more' ) }
							</a>
						) }
					</div>
					<p className="reader-summary__caption">
						<svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M2.05273 0.726562H18.834C19.1777 0.726562 19.459 0.851563 19.6777 1.10156C19.9277 1.32031 20.0527 1.60156 20.0527 1.94531V13.9453C20.0527 14.2578 19.9277 14.5391 19.6777 14.7891C19.459 15.0078 19.1777 15.1172 18.834 15.1172H12.834V17.5547H15.2246C15.5684 17.5547 15.8496 17.6797 16.0684 17.9297C16.3184 18.1484 16.4434 18.4141 16.4434 18.7266V19.9453H4.44336V18.7266C4.44336 18.4141 4.55273 18.1484 4.77148 17.9297C5.02148 17.6797 5.31836 17.5547 5.66211 17.5547H8.05273V15.1172H2.05273C1.70898 15.1172 1.41211 15.0078 1.16211 14.7891C0.943359 14.5391 0.833984 14.2578 0.833984 13.9453V1.94531C0.833984 1.60156 0.943359 1.32031 1.16211 1.10156C1.41211 0.851563 1.70898 0.726562 2.05273 0.726562ZM17.6621 11.5547V3.11719H3.22461V11.5547H17.6621ZM4.44336 4.33594H15.2246L4.44336 9.11719V4.33594Z" fill="black" fillOpacity="0.87" />
						</svg>
						{ __( 'Desktop origin visitors - Screenshot', 'amp' ) }
					</p>
				</div>
				<div>
					<div className="selectable selectable--bottom">
						<Phone>
							<img
								src={ readerThemeData.screenshot_url }
								alt={ readerThemeData.name }
								loading="lazy"
								decoding="async"
								height="2165"
								width="1000"
							/>
						</Phone>

						<h3>
							{ readerThemeData.name }
						</h3>
						<p>
							{ readerThemeData.description }
						</p>
						{ readerThemeData.homepage && readerThemeData.homepage.length && (
							<a href={ readerThemeData.homepage }>
								{ __( 'Learn more' ) }
							</a>
						) }
					</div>
					<p className="reader-summary__caption">
						<svg width="13" height="20" viewBox="0 0 13 20" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M1.66211 0.265625H11.2246C11.5684 0.265625 11.8496 0.390625 12.0684 0.640625C12.3184 0.859375 12.4434 1.14063 12.4434 1.48438V18.2656C12.4434 18.6094 12.3184 18.9062 12.0684 19.1562C11.8496 19.375 11.5684 19.4844 11.2246 19.4844H1.66211C1.31836 19.4844 1.02148 19.375 0.771484 19.1562C0.552734 18.9062 0.443359 18.6094 0.443359 18.2656V1.48438C0.443359 1.14063 0.552734 0.859375 0.771484 0.640625C1.02148 0.390625 1.31836 0.265625 1.66211 0.265625ZM10.0527 14.6562V2.65625H2.83398V14.6562H10.0527ZM4.05273 3.875H8.83398L4.05273 9.875V3.875Z" fill="black" fillOpacity="0.87" />
						</svg>

						{ __( 'Mobile/AMP reader mode visitors - Screenshot', 'amp' ) }
					</p>
				</div>
			</div>
		</div>
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
