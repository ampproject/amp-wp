/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';
import { AMP_COMPATIBLE_THEMES_URL } from 'amp-settings'; // From WP inline script.

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement, useMemo } from '@wordpress/element';
import { ExternalLink } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { useNormalizedThemesData } from '../themes-context-provider/use-normalized-themes-data';
import { IconWebsitePaintBrush } from '../svg/website-paint-brush';
import { isExternalUrl } from '../../common/helpers/is-external-url';
import { SiteScanSourcesList } from './site-scan-sources-list';
import { SiteScanResults } from './index';

/**
 * Render a list of themes that cause AMP Incompatibility.
 *
 * @param {Object}   props              Component props.
 * @param {string}   props.className    Component class name.
 * @param {boolean}  props.showHelpText Show additional help text above the issues list.
 * @param {string[]} props.slugs        List of theme slugs.
 */
export function ThemesWithAmpIncompatibility({
	className,
	showHelpText = false,
	slugs = [],
	...props
}) {
	const themesData = useNormalizedThemesData();
	const sources = useMemo(
		() =>
			slugs?.map((slug) => {
				const currentThemeData = themesData?.[slug];

				if (!currentThemeData) {
					return {
						slug,
						status: 'uninstalled',
					};
				}

				const isCurrentThemeParent =
					themesData?.[currentThemeData?.child]?.status === 'active';

				return {
					...currentThemeData,
					status: isCurrentThemeParent
						? 'active'
						: currentThemeData.status,
				};
			}),
		[slugs, themesData]
	);

	return (
		<SiteScanResults
			title={__('Themes with AMP incompatibility', 'amp')}
			icon={<IconWebsitePaintBrush />}
			count={slugs.length}
			className={classnames('site-scan-results--themes', className)}
			{...props}
		>
			{showHelpText && (
				<p>
					{createInterpolateElement(
						__(
							'Because of theme issue(s) we’ve found, you’ll want to consider a different <a>template mode</a> below.',
							'amp'
						),
						{
							// eslint-disable-next-line jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string.
							a: <a href="#template-modes" />,
						}
					)}
					{AMP_COMPATIBLE_THEMES_URL
						? createInterpolateElement(
								` ${__(
									'You may also want to browse alternative <a>AMP compatible themes</a>.',
									'amp'
								)}`,
								{
									a: isExternalUrl(
										AMP_COMPATIBLE_THEMES_URL
									) ? (
										<ExternalLink
											href={AMP_COMPATIBLE_THEMES_URL}
										/>
									) : (
										// eslint-disable-next-line jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string.
										<a href={AMP_COMPATIBLE_THEMES_URL} />
									),
								}
						  )
						: ''}
				</p>
			)}
			<SiteScanSourcesList
				sources={sources}
				inactiveSourceNotice={__(
					'This theme has been deactivated since last site scan.'
				)}
				uninstalledSourceNotice={__(
					'This theme has been uninstalled or its metadata is unavailable.'
				)}
			/>
		</SiteScanResults>
	);
}

ThemesWithAmpIncompatibility.propTypes = {
	className: PropTypes.string,
	showHelpText: PropTypes.bool,
	slugs: PropTypes.arrayOf(PropTypes.string).isRequired,
};
