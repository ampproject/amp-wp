/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Transitional as TransitionalIllustration } from '../../components/svg/transitional';
import { RedirectToggle } from './redirect-toggle';
import { SummaryHeader } from './summary-header';
import { DesktopScreenshot } from './desktop-screenshot';

/**
 * Summary screen when transitional mode was selected.
 *
 * @param {Object} props
 * @param {Object} props.currentTheme Data for the theme currently active on the site.
 */
export function Transitional( { currentTheme } ) {
	return (
		<>
			<SummaryHeader
				illustration={ <TransitionalIllustration /> }
				title={ __( 'Transitional', 'amp' ) }
				text={ __( 'In Transitional mode <b>your site will have a non-AMP and an AMP version</b>, and <b>both will use the same theme</b>. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' ) }
			/>

			<DesktopScreenshot { ...currentTheme } />

			<RedirectToggle />

		</>
	);
}

Transitional.propTypes = {
	currentTheme: PropTypes.shape( {
		description: PropTypes.string,
		name: PropTypes.string,
		screenshot: PropTypes.string,
		url: PropTypes.string,
	} ).isRequired,
};
