/**
 * External dependencies
 */
import { SETTINGS_LINK } from 'amp-settings'; // From WP inline script.

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	createInterpolateElement,
	useContext,
	useEffect,
} from '@wordpress/element';

/**
 * Internal dependencies
 */
import './style.scss';
import {
	AMPNotice,
	NOTICE_SIZE_LARGE,
	NOTICE_TYPE_SUCCESS,
	NOTICE_TYPE_INFO,
} from '../../../components/amp-notice';
import { Navigation } from '../../components/navigation-context-provider';
import { Options } from '../../../components/options-context-provider';
import { ReaderThemes } from '../../../components/reader-themes-context-provider';
import { SiteScan } from '../../../components/site-scan-context-provider';
import { User } from '../../../components/user-context-provider';
import { IconLaptopToggles } from '../../../components/svg/icon-laptop-toggles';
import { IconLaptopSearch } from '../../../components/svg/icon-laptop-search';
import { AMPSettingToggle } from '../../../components/amp-setting-toggle';
import { NavMenu } from '../../../components/nav-menu';
import { READER, STANDARD, TRANSITIONAL } from '../../../common/constants';
import { Preview } from './preview';
import { Saving } from './saving';
import { usePreview } from './use-preview';

/**
 * Final screen, where data is saved.
 */
export function Done() {
	const {
		didSaveOptions,
		editedOptions: {
			theme_support: themeSupport,
			reader_theme: readerTheme,
		},
		hasOptionsChanges,
		readerModeWasOverridden,
		saveOptions,
		savingOptions,
	} = useContext(Options);
	const {
		didSaveDeveloperToolsOption,
		saveDeveloperToolsOption,
		savingDeveloperToolsOption,
	} = useContext(User);
	const { canGoForward, setCanGoForward } = useContext(Navigation);
	const { downloadedTheme, downloadingTheme, downloadingThemeError } =
		useContext(ReaderThemes);
	const { fetchScannableUrls, forceStandardMode, isFetchingScannableUrls } =
		useContext(SiteScan);
	const {
		hasPreview,
		isPreviewingAMP,
		previewLinks,
		previewUrl,
		setActivePreviewLink,
		toggleIsPreviewingAMP,
	} = usePreview();

	/**
	 * Allow the finish button to be enabled.
	 */
	useEffect(() => {
		if (!canGoForward) {
			setCanGoForward(true);
		}
	}, [setCanGoForward, canGoForward]);

	/**
	 * Triggers saving of options on arrival to this screen.
	 */
	useEffect(() => {
		if (!didSaveOptions && !savingOptions) {
			saveOptions();
		}
	}, [didSaveOptions, saveOptions, savingOptions]);

	/**
	 * Refetches the scannable URLs if they have been requested with Standard mode forced last time.
	 */
	useEffect(() => {
		if (didSaveOptions && forceStandardMode) {
			fetchScannableUrls();
		}
	}, [didSaveOptions, fetchScannableUrls, forceStandardMode]);

	/**
	 * Triggers saving of user options on arrival of this screen.
	 */
	useEffect(() => {
		if (!didSaveDeveloperToolsOption && !savingDeveloperToolsOption) {
			saveDeveloperToolsOption();
		}
	}, [
		didSaveDeveloperToolsOption,
		savingDeveloperToolsOption,
		saveDeveloperToolsOption,
	]);

	if (
		savingOptions ||
		savingDeveloperToolsOption ||
		downloadingTheme ||
		hasOptionsChanges ||
		isFetchingScannableUrls
	) {
		return <Saving />;
	}

	return (
		<div className="done">
			<h1 className="done__heading">{__('Done', 'amp')}</h1>
			<div className="done__content done__content--primary">
				<h2 className="done__icon-title">
					<IconLaptopSearch />
					{__('Review', 'amp')}
				</h2>
				{READER === themeSupport && downloadedTheme === readerTheme && (
					<AMPNotice
						size={NOTICE_SIZE_LARGE}
						type={NOTICE_TYPE_SUCCESS}
					>
						{__(
							'Your Reader theme was automatically installed',
							'amp'
						)}
					</AMPNotice>
				)}
				{readerModeWasOverridden && (
					<AMPNotice type={NOTICE_TYPE_INFO} size={NOTICE_SIZE_LARGE}>
						{__(
							"Because you selected a Reader theme that is the same as your site's active theme, your site has automatically been switched to Transitional template mode.",
							'amp'
						)}
					</AMPNotice>
				)}
				<p>
					{__(
						'Your site is ready to bring great experiences to your users!',
						'amp'
					)}
				</p>
				{STANDARD === themeSupport && (
					<>
						<p>
							{__(
								'In Standard mode there is a single AMP version of your site.',
								'amp'
							)}
						</p>
						{hasPreview && (
							<p>
								{__(
									'Browse your site here to ensure it meets your expectations.',
									'amp'
								)}
							</p>
						)}
					</>
				)}
				{TRANSITIONAL === themeSupport && (
					<>
						<p>
							{__(
								'In Transitional mode AMP and non-AMP versions of your site are served using your currently active theme.',
								'amp'
							)}
						</p>
						{hasPreview && (
							<p>
								{__(
									'Browse your site here to ensure it meets your expectations, and toggle the AMP setting to compare both versions.',
									'amp'
								)}
							</p>
						)}
					</>
				)}
				{READER === themeSupport && (
					<>
						<p>
							{__(
								'In Reader mode AMP is served using your selected Reader theme, and pages for your non-AMP site are served using your primary theme.',
								'amp'
							)}
						</p>
						{hasPreview && (
							<p>
								{__(
									'Browse your site here to ensure it meets your expectations, and toggle the AMP setting to compare both versions.',
									'amp'
								)}
							</p>
						)}
						<p>
							{__(
								'As a last step, use the Customizer to tailor the Reader theme as needed.',
								'amp'
							)}
						</p>
					</>
				)}
				{hasPreview && (
					<div className="done__links-container">
						<NavMenu
							links={previewLinks}
							onClick={(e, link) => {
								e.preventDefault();
								setActivePreviewLink(link);
							}}
						/>
					</div>
				)}
			</div>
			<div className="done__preview-container">
				{READER === themeSupport && downloadingThemeError && (
					<AMPNotice size={NOTICE_SIZE_LARGE} type={NOTICE_TYPE_INFO}>
						{__(
							'There was an error downloading your Reader theme. As a result, your site is currently using the legacy reader theme. Please install your chosen theme manually.',
							'amp'
						)}
					</AMPNotice>
				)}
				{hasPreview && (
					<>
						{STANDARD !== themeSupport && (
							<AMPSettingToggle
								text={__('AMP', 'amp')}
								checked={isPreviewingAMP}
								onChange={toggleIsPreviewingAMP}
								compact={true}
							/>
						)}
						<Preview url={previewUrl} />
					</>
				)}
			</div>
			<div className="done__content done__content--secondary">
				<h2 className="done__icon-title">
					<IconLaptopToggles />
					{__('Need help?', 'amp')}
				</h2>
				<ul className="done__list">
					<li>
						{createInterpolateElement(
							__('Reach out in the <a>support forums</a>', 'amp'),
							{
								a: (
									// eslint-disable-next-line jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string.
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
							__(
								'Try a different template mode <a>in settings</a>',
								'amp'
							),
							{
								a: (
									// eslint-disable-next-line jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string.
									<a
										href={SETTINGS_LINK}
										target="_blank"
										rel="noreferrer noopener"
									/>
								),
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
								a: (
									// eslint-disable-next-line jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string.
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
			</div>
		</div>
	);
}
