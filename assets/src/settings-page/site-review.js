/**
 * External dependencies
 */
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
import { AMPDrawer } from '../components/amp-drawer';
import { IconLaptopSearch } from '../components/svg/icon-laptop-search';
import { Options } from '../components/options-context-provider';
import { User } from '../components/user-context-provider';
import { READER, STANDARD, TRANSITIONAL } from '../common/constants';

/**
 * Review component on the settings screen.
 */
export function SiteReview() {
	const {
		reviewPanelDismissedForTemplateMode,
		saveReviewPanelDismissedForTemplateMode,
		savingReviewPanelDismissedForTemplateMode,
	} = useContext( User );
	const { originalOptions } = useContext( Options );
	const {
		paired_url_examples: pairedUrlExamples,
		paired_url_structure: pairedUrlStructure,
		theme_support: themeSupport,
	} = originalOptions;

	if ( savingReviewPanelDismissedForTemplateMode || reviewPanelDismissedForTemplateMode === themeSupport ) {
		return null;
	}

	const previewPermalink = STANDARD === themeSupport ? HOME_URL : pairedUrlExamples[ pairedUrlStructure ][ 0 ];

	return (
		<AMPDrawer
			heading={ (
				<>
					<IconLaptopSearch width={ 55 } />
					{ __( 'Review', 'amp' ) }
				</>
			) }
			hiddenTitle={ __( 'Review', 'amp' ) }
			id="site-review"
			initialOpen={ true }
		>
			<div className="settings-site-review">
				<p>
					{ __( 'Your site is ready to bring great experiences to your users!', 'amp' ) }
				</p>
				{ STANDARD === themeSupport && (
					<p>
						{ __( 'In Standard mode there is a single AMP version of your site. Browse your site below to ensure it meets your expectations.', 'amp' ) }
					</p>
				) }
				{ TRANSITIONAL === themeSupport && (
					<>
						<p>
							{ __( 'In Transitional mode AMP and non-AMP versions of your site are served using your currently active theme.', 'amp' ) }
						</p>
						<p>
							{ __( 'Browse your site below to ensure it meets your expectations, and toggle the AMP setting to compare both versions.', 'amp' ) }
						</p>
					</>
				) }
				{ READER === themeSupport && (
					<p>
						{ __( 'In Reader mode AMP is served using your selected Reader theme, and pages for your non-AMP site are served using your primary theme. Browse your site below to ensure it meets your expectations, and toggle the AMP setting to compare both versions.', 'amp' ) }
					</p>
				) }
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
					<Button
						onClick={ () => {
							saveReviewPanelDismissedForTemplateMode( themeSupport );
						} }
						isLink={ true }
					>
						{ __( 'Dismiss', 'amp' ) }
					</Button>
				</div>
			</div>
		</AMPDrawer>
	);
}
