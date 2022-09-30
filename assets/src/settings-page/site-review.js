/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { createInterpolateElement, useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { IconLaptopToggles } from '../components/svg/icon-laptop-toggles';
import { AMPDrawer } from '../components/amp-drawer';
import { IconLaptopSearch } from '../components/svg/icon-laptop-search';
import { Options } from '../components/options-context-provider';
import { User } from '../components/user-context-provider';
import { READER, STANDARD, TRANSITIONAL } from '../common/constants';
import { SiteScan as SiteScanContext } from '../components/site-scan-context-provider';

/**
 * Review component on the settings screen.
 */
export function SiteReview() {
	const {
		reviewPanelDismissedForTemplateMode,
		saveReviewPanelDismissedForTemplateMode,
		savingReviewPanelDismissedForTemplateMode,
	} = useContext(User);
	const { previewPermalink } = useContext(SiteScanContext);
	const { originalOptions } = useContext(Options);
	const { theme_support: themeSupport } = originalOptions;

	if (
		savingReviewPanelDismissedForTemplateMode ||
		reviewPanelDismissedForTemplateMode === themeSupport
	) {
		return null;
	}

	return (
		<AMPDrawer
			heading={
				<>
					<IconLaptopSearch width={55} />
					{__('Review', 'amp')}
				</>
			}
			hiddenTitle={__('Review', 'amp')}
			id="site-review"
			initialOpen={true}
		>
			<div className="settings-site-review">
				<p>
					{__(
						'Your site is ready to bring great experiences to your users!',
						'amp'
					)}
				</p>
				{STANDARD === themeSupport && (
					<p>
						{__(
							'In Standard mode there is a single AMP version of your site. Browse your site below to ensure it meets your expectations.',
							'amp'
						)}
					</p>
				)}
				{TRANSITIONAL === themeSupport && (
					<>
						<p>
							{__(
								'In Transitional mode AMP and non-AMP versions of your site are served using your currently active theme.',
								'amp'
							)}
						</p>
						<p>
							{__(
								'Browse your site below to ensure it meets your expectations, and toggle the AMP setting to compare both versions.',
								'amp'
							)}
						</p>
					</>
				)}
				{READER === themeSupport && (
					<p>
						{__(
							'In Reader mode AMP is served using your selected Reader theme, and pages for your non-AMP site are served using your primary theme. Browse your site below to ensure it meets your expectations, and toggle the AMP setting to compare both versions.',
							'amp'
						)}
					</p>
				)}
				<h3 className="settings-site-review__heading">
					<IconLaptopToggles />
					{__('Need help?', 'amp')}
				</h3>
				<ul className="settings-site-review__list">
					<li>
						{createInterpolateElement(
							__('Reach out in the <a>support forums</a>', 'amp'),
							{
								// eslint-disable-next-line jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string.
								a: (
									<a
										href="https://wordpress.org/support/plugin/amp/#new-topic-0"
										target="_blank"
										rel="noreferrer noopener"
									/>
								),
							}
						)}
					</li>
					<li>
						{createInterpolateElement(
							__('Try a different <a>template mode</a>', 'amp'),
							{
								// eslint-disable-next-line jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string.
								a: <a href="#template-modes" />,
							}
						)}
					</li>
					<li>
						{createInterpolateElement(
							__(
								'<a>Learn more</a> how the AMP plugin works',
								'amp'
							),
							{
								// eslint-disable-next-line jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string.
								a: (
									<a
										href="https://amp-wp.org/documentation/how-the-plugin-works/"
										target="_blank"
										rel="noreferrer noopener"
									/>
								),
							}
						)}
					</li>
				</ul>
				<div className="settings-site-review__actions">
					{previewPermalink && (
						<Button href={previewPermalink} isPrimary={true}>
							{__('Browse Site', 'amp')}
						</Button>
					)}
					<Button
						onClick={() => {
							saveReviewPanelDismissedForTemplateMode(
								themeSupport
							);
						}}
						isLink={true}
					>
						{__('Dismiss', 'amp')}
					</Button>
				</div>
			</div>
		</AMPDrawer>
	);
}
