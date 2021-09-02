/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { HOME_URL } from 'amp-settings';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { IconLaptopToggles } from '../components/svg/icon-laptop-toggles';
import { Options } from '../components/options-context-provider';

/**
 * Review component on the settings screen.
 *
 * @param {Object}   props         Component props.
 * @param {Function} props.dismiss Dismiss handler.
 */
export function SiteReview( { dismiss } ) {
	const { originalOptions } = useContext( Options );
	const {
		paired_url_examples: pairedUrlExamples,
		paired_url_structure: pairedUrlStructure,
		theme_support: themeSupport,
	} = originalOptions;
	const previewPermalink = 'standard' === themeSupport ? HOME_URL : pairedUrlExamples[ pairedUrlStructure ][ 0 ];

	return (
		<div className="settings-site-review">
			<p>
				{ __( 'Your site is ready to bring great experiences to your users! In Standard mode there is a single AMP version of your site. Browse your site and ensure the functionality and look-and-feel are as expected.', 'amp' ) }
			</p>
			<h3 className="settings-site-review__heading">
				<IconLaptopToggles />
				{ __( 'Need help?', 'amp' ) }
			</h3>
			<ul className="settings-site-review__list">
				{ /* dangerouslySetInnerHTML reason: Injection of a link. */ }
				<li dangerouslySetInnerHTML={ {
					__html: sprintf(
						/* translators: placeholder is a link to support forum. */
						__( 'Reach out in the <a href="%s" target="_blank" rel="noreferrer noopener">support forums</a>', 'amp' ),
						'https://wordpress.org/support/plugin/amp/#new-topic-0',
					),
				} } />
				{ /* dangerouslySetInnerHTML reason: Injection of a link. */ }
				<li dangerouslySetInnerHTML={ {
					__html: sprintf(
						/* translators: placeholder is the template mode section anchor. */
						__( 'Try a different <a href="%s">template mode</a>', 'amp' ),
						'#template-modes',
					),
				} } />
				{ /* dangerouslySetInnerHTML reason: Injection of a link. */ }
				<li dangerouslySetInnerHTML={ {
					__html: sprintf(
						/* translators: placeholder is a link to the plugin site. */
						__( '<a href="%s" target="_blank" rel="noreferrer noopener">Learn more</a> how the PX plugin works', 'amp' ),
						'https://amp-wp.org/documentation/how-the-plugin-works/',
					),
				} } />
			</ul>
			<div className="settings-site-review__actions">
				<Button href={ previewPermalink } isPrimary={ true }>
					{ __( 'Browse Site', 'amp' ) }
				</Button>
				<Button onClick={ dismiss } isLink={ true }>
					{ __( 'Dismiss', 'amp' ) }
				</Button>
			</div>
		</div>
	);
}

SiteReview.propTypes = {
	dismiss: PropTypes.func,
};
