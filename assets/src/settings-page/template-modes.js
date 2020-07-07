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
import { TemplateModeOption } from '../components/template-mode-option';
import '../css/template-mode-selection.css';
import { AMPNotice, NOTICE_SIZE_LARGE, NOTICE_TYPE_INFO } from '../components/amp-notice';

/**
 * Template modes section of the settings page.
 *
 * @param {Object} props Component props.
 * @param {Object} props.themeSupportNotices Notices to show inside the template mode options, passed from the back end.
 */
export function TemplateModes( { themeSupportNotices } ) {
	return (
		<section className="template-mode-selection">
			<h2>
				{ __( 'Template mode', 'amp' ) }
			</h2>
			<p dangerouslySetInnerHTML={ {
				__html: __( 'For a list of themes and plugins that are known to be AMP compatible, please see the <a href="https://amp-wp.org/ecosystem/">ecosystem page</a>.', 'amp' ),
			} } />
			<TemplateModeOption
				details={ __( 'In Standard Mode your site uses a single theme and there is a single version of your content. In this mode, AMP is the framework of your site and there is reduced development and maintenance costs by having a single site to maintain.', 'amp' ) }
				mode="standard"
			>
				{ themeSupportNotices.standard && (
					<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
						<p>
							{ themeSupportNotices.standard }
						</p>
					</AMPNotice>
				) }
			</TemplateModeOption>
			<TemplateModeOption
				details={ __( 'The active theme\'s templates are used to generate non-AMP and AMP versions of your content, allowing for each canonical URL to have a corresponding (paired) AMP URL. This mode is useful to progressively transition towards a fully AMP-first site. Depending on your themes/plugins, a varying level of development work may be required.', 'amp' ) }
				mode="transitional"
			>
				{ themeSupportNotices.transitional && (
					<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
						<p>
							{ themeSupportNotices.transitional }
						</p>
					</AMPNotice>
				) }
			</TemplateModeOption>
			<TemplateModeOption
				details={ __( 'Formerly called classic mode, this mode generates paired AMP content using simplified templates which may not match the look and feel of your site. Only posts/pages can be served as AMP in Reader mode. No reidrection is performed for mobile visitors; AMP pages are served by AMP consumption platforms.', 'amp' ) }
				mode="reader"
			>
				{ themeSupportNotices.reader && (
					<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
						<p>
							{ themeSupportNotices.reader }
						</p>
					</AMPNotice>
				) }
			</TemplateModeOption>
		</section>
	);
}
TemplateModes.propTypes = {
	themeSupportNotices: PropTypes.shape( {
		reader: PropTypes.string.isRequired,
		standard: PropTypes.string.isRequired,
		transitional: PropTypes.string.isRequired,
	} ).isRequired,
};
