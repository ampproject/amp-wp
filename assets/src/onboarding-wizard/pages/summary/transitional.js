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
import { useContext } from '@wordpress/element';
import { Transitional as TransitionalIllustration } from '../../../components/svg/transitional';
import { AMPNotice, NOTICE_TYPE_INFO, NOTICE_SIZE_LARGE } from '../../../components/amp-notice';
import { Options } from '../../../components/options-context-provider';
import { SummaryHeader } from './summary-header';
import { DesktopScreenshot } from './desktop-screenshot';

/**
 * Summary screen when transitional mode was selected.
 *
 * @param {Object} props
 * @param {Object} props.currentTheme Data for the theme currently active on the site.
 */
export function Transitional( { currentTheme } ) {
	const { readerModeWasOverridden } = useContext( Options );

	return (
		<>
			{ readerModeWasOverridden && (
				<AMPNotice type={ NOTICE_TYPE_INFO } size={ NOTICE_SIZE_LARGE }>
					{ __( 'Because you selected a Reader theme that is the same as your site\'s active theme, your site has automatically been switched to Transitional template mode.', 'amp' ) }
				</AMPNotice>
			) }
			<SummaryHeader
				illustration={ <TransitionalIllustration /> }
				title={ __( 'Transitional', 'amp' ) }
				text={ __( 'In Transitional mode <b>your site will have a non-AMP and an AMP version</b>, and <b>both will use the same theme</b>. If automatic mobile redirection is enabled, the AMP version of the content will be served on mobile devices. If AMP-to-AMP linking is enabled, once users are on an AMP page, they will continue navigating your AMP content.', 'amp' ) }
			/>

			<DesktopScreenshot { ...currentTheme } />
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
