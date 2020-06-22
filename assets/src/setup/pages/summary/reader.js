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

export function Reader( { currentTheme } ) {
	const { options, updateOptions } = useContext( Options );
	const { themes } = useContext( ReaderThemes );

	const { reader_theme: readerTheme, mobile_redirect: mobileRedirect } = options;

	const readerThemeData = themes ? themes.find( ( theme ) => theme.slug === readerTheme ) : {};

	return (
		<div className="reader-summary">
			<p>
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
				<div className="selectable selectable--bottom">
					<Desktop>
						<img src={ currentTheme.screenshot } alt={ currentTheme.name } />

					</Desktop>
				</div>
				<div className="selectable selectable--bottom">
					<Phone>
						<img src={ readerThemeData.screenshot_url } alt={ readerThemeData.name } />
					</Phone>
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
	} ).isRequired,
};
