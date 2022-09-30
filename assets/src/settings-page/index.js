/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import {
	CURRENT_THEME,
	HAS_DEPENDENCY_SUPPORT,
	OPTIONS_REST_PATH,
	READER_THEMES_REST_PATH,
	SCANNABLE_URLS_REST_PATH,
	UPDATES_NONCE,
	USER_FIELD_DEVELOPER_TOOLS_ENABLED,
	USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE,
	USERS_RESOURCE_REST_PATH,
	VALIDATE_NONCE,
	VALIDATED_URLS_LINK,
	ERROR_INDEX_LINK,
} from 'amp-settings';

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import {
	render,
	useContext,
	useState,
	useEffect,
	useCallback,
} from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import '../css/variables.css';
import '../css/elements.css';
import '../css/core-components.css';
import './style.css';
import {
	OptionsContextProvider,
	Options,
} from '../components/options-context-provider';
import {
	ReaderThemesContextProvider,
	ReaderThemes,
} from '../components/reader-themes-context-provider';
import { SiteSettingsProvider } from '../components/site-settings-provider';
import { Loading } from '../components/loading';
import { UnsavedChangesWarning } from '../components/unsaved-changes-warning';
import { ErrorBoundary } from '../components/error-boundary';
import { ErrorContextProvider } from '../components/error-context-provider';
import { AMPDrawer } from '../components/amp-drawer';
import { AMPNotice, NOTICE_SIZE_LARGE } from '../components/amp-notice';
import { ErrorScreen } from '../components/error-screen';
import { User, UserContextProvider } from '../components/user-context-provider';
import { PluginsContextProvider } from '../components/plugins-context-provider';
import { ThemesContextProvider } from '../components/themes-context-provider';
import {
	SiteScanContextProvider,
	SiteScan as SiteScanContext,
} from '../components/site-scan-context-provider';
import { Welcome } from './welcome';
import { Sandboxing } from './sandboxing';
import { TemplateModes } from './template-modes';
import { SupportedTemplates } from './supported-templates';
import { SettingsFooter } from './settings-footer';
import { SiteReview } from './site-review';
import { PluginSuppression } from './plugin-suppression';
import { Analytics } from './analytics';
import { PairedUrlStructure } from './paired-url-structure';
import { MobileRedirection } from './mobile-redirection';
import { DeveloperTools } from './developer-tools';
import { SiteScan } from './site-scan';
import { ToggleUseNativeImgTag } from './toggle-use-native-img-tag';
import { DeleteDataAtUninstall } from './delete-data-at-uninstall';

const { ajaxurl: wpAjaxUrl } = global;

let errorHandler;

/**
 * Context providers for the settings page.
 *
 * @param {Object} props          Component props.
 * @param {any}    props.children Context consumers.
 */
function Providers({ children }) {
	global.removeEventListener('error', errorHandler);

	return (
		<ErrorContextProvider>
			<ErrorBoundary>
				<SiteSettingsProvider hasErrorBoundary={true}>
					<OptionsContextProvider
						hasErrorBoundary={true}
						optionsRestPath={OPTIONS_REST_PATH}
						populateDefaultValues={true}
					>
						<UserContextProvider
							onlyFetchIfPluginIsConfigured={false}
							userOptionDeveloperTools={
								USER_FIELD_DEVELOPER_TOOLS_ENABLED
							}
							userFieldReviewPanelDismissedForTemplateMode={
								USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE
							}
							usersResourceRestPath={USERS_RESOURCE_REST_PATH}
						>
							<ReaderThemesContextProvider
								currentTheme={CURRENT_THEME}
								readerThemesRestPath={READER_THEMES_REST_PATH}
								hasErrorBoundary={true}
								hideCurrentlyActiveTheme={true}
								updatesNonce={UPDATES_NONCE}
								wpAjaxUrl={wpAjaxUrl}
							>
								<PluginsContextProvider>
									<ThemesContextProvider>
										<SiteScanContextProvider
											fetchCachedValidationErrors={true}
											refetchPluginSuppressionOnScanComplete={
												true
											}
											resetOnOptionsChange={true}
											scannableUrlsRestPath={
												SCANNABLE_URLS_REST_PATH
											}
											validateNonce={VALIDATE_NONCE}
										>
											{children}
										</SiteScanContextProvider>
									</ThemesContextProvider>
								</PluginsContextProvider>
							</ReaderThemesContextProvider>
						</UserContextProvider>
					</OptionsContextProvider>
				</SiteSettingsProvider>
			</ErrorBoundary>
		</ErrorContextProvider>
	);
}
Providers.propTypes = {
	children: PropTypes.any,
};

/**
 * Scrolls to the first focusable element in a section, or to the section if no focusable elements are found.
 *
 * @param {string} focusedSectionId A section ID.
 */
function scrollFocusedSectionIntoView(focusedSectionId) {
	if (!focusedSectionId) {
		return;
	}

	const focusedSectionElement = document.getElementById(focusedSectionId);
	if (!focusedSectionElement) {
		return;
	}

	focusedSectionElement.scrollIntoView();

	const firstInput = focusedSectionElement.querySelector(
		'input, select, textarea, button'
	);
	if (firstInput) {
		firstInput.focus();
	}
}

/**
 * Get the element for an AMP admin submenu if it exists.
 *
 * @param {string} href Link href.
 * @return {Element|null} LI element or null if not found.
 */
function getAmpAdminMenuItem(href) {
	const parsedUrl = new URL(href);
	const searchParams = new Map(
		new URLSearchParams(parsedUrl.search).entries()
	);

	links: for (const a of document.querySelectorAll(
		'#toplevel_page_amp-options ul > li > a'
	)) {
		if (!a.pathname.endsWith(parsedUrl.pathname)) {
			continue;
		}

		const linkSearchParams = new URLSearchParams(parsedUrl.search);
		for (const [key, value] of searchParams.entries()) {
			if (linkSearchParams.get(key) !== value) {
				continue links;
			}
		}

		return a.parentNode;
	}
	return null;
}

/**
 * Add an item to the AMP admin submenu.
 *
 * @param {string} title Link text.
 * @param {string} href  Link href.
 */
function addAmpAdminMenuItem(title, href) {
	const ul = document.querySelector('#toplevel_page_amp-options ul');
	if (!ul || getAmpAdminMenuItem(href)) {
		return;
	}

	const li = document.createElement('li');
	const a = document.createElement('a');
	a.innerText = title;
	a.href = href;
	li.appendChild(a);
	ul.appendChild(li);
}

/**
 * Remove an item from the AMP admin submenu.
 *
 * @param {string} href Link href.
 */
function removeAmpAdminMenuItem(href) {
	const li = getAmpAdminMenuItem(href);
	if (li) {
		li.remove();
	}
}

/**
 * Settings page application root.
 *
 * @param {Object}  props
 * @param {Element} props.appRoot App root.
 */
function Root({ appRoot }) {
	const [focusedSection, setFocusedSection] = useState(
		global.location.hash.replace(/^#/, '')
	);

	const { hasOptionsChanges, fetchingOptions, saveOptions } =
		useContext(Options);
	const {
		hasDeveloperToolsOptionChange,
		saveDeveloperToolsOption,
		developerToolsOption,
	} = useContext(User);
	const { templateModeWasOverridden } = useContext(ReaderThemes);
	const { isSkipped, isFetchingScannableUrls } = useContext(SiteScanContext);

	/**
	 * Handle the form submit event.
	 */
	const onSubmit = useCallback(
		(event) => {
			event.preventDefault();

			if (hasOptionsChanges) {
				saveOptions();
			}

			if (hasDeveloperToolsOptionChange) {
				saveDeveloperToolsOption();
				if (developerToolsOption) {
					addAmpAdminMenuItem(
						__('Validated URLs', 'amp'),
						VALIDATED_URLS_LINK
					);
					addAmpAdminMenuItem(
						__('Error Index', 'amp'),
						ERROR_INDEX_LINK
					);
				} else {
					removeAmpAdminMenuItem(VALIDATED_URLS_LINK);
					removeAmpAdminMenuItem(ERROR_INDEX_LINK);
				}
			}
		},
		[
			hasDeveloperToolsOptionChange,
			hasOptionsChanges,
			saveDeveloperToolsOption,
			saveOptions,
			developerToolsOption,
		]
	);

	/**
	 * Scroll to the focused element on load or when it changes.
	 */
	useEffect(() => {
		if (fetchingOptions || null === templateModeWasOverridden) {
			return;
		}

		scrollFocusedSectionIntoView(focusedSection);
	}, [
		fetchingOptions,
		isFetchingScannableUrls,
		focusedSection,
		templateModeWasOverridden,
	]);

	/**
	 * Resets the focused element state when the hash changes on the page.
	 */
	useEffect(() => {
		const hashChangeCallback = (event = null) => {
			if (event) {
				event.preventDefault();
			}

			// Ensure this runs after state updates.
			const newFocusedSection = global.location.hash.replace(/^#/, '');
			setFocusedSection(newFocusedSection);
		};

		hashChangeCallback();
		global.addEventListener('hashchange', hashChangeCallback);

		return () => {
			global.removeEventListener('hashchange', hashChangeCallback);
		};
	}, [fetchingOptions]);

	const focusSiteScanSection = useCallback(() => {
		setFocusedSection('site-scan');
	}, []);

	if (false !== fetchingOptions || null === templateModeWasOverridden) {
		return <Loading />;
	}

	return (
		<>
			{!HAS_DEPENDENCY_SUPPORT && (
				<AMPNotice
					className="not-has-dependency-support"
					size={NOTICE_SIZE_LARGE}
				>
					{__(
						'You are using an old version of WordPress. Please upgrade to access all of the features of the AMP plugin.',
						'amp'
					)}
				</AMPNotice>
			)}
			<Welcome />
			{!isSkipped && <SiteScan onSiteScan={focusSiteScanSection} />}
			<SiteReview />
			<form onSubmit={onSubmit}>
				<TemplateModes
					focusReaderThemes={'reader-themes' === focusedSection}
				/>
				<h2 id="advanced-settings">
{__('Advanced Settings', 'amp')}
</h2>
				<AMPDrawer
					heading={<h3>
{__('Supported Templates', 'amp')}
</h3>}
					hiddenTitle={__('Supported templates', 'amp')}
					id="supported-templates"
					initialOpen={'supported-templates' === focusedSection}
				>
					<SupportedTemplates />
				</AMPDrawer>
				<AMPDrawer
					heading={<h3>
{__('Plugin Suppression', 'amp')}
</h3>}
					hiddenTitle={__('Plugin suppression', 'amp')}
					id="plugin-suppression"
					initialOpen={'plugin-suppression' === focusedSection}
				>
					<PluginSuppression />
				</AMPDrawer>
				<AMPDrawer
					className="amp-analytics"
					heading={<h3>
{__('Analytics', 'amp')}
</h3>}
					hiddenTitle={__('Analytics', 'amp')}
					id="analytics-options"
					initialOpen={'analytics-options' === focusedSection}
				>
					<Analytics />
				</AMPDrawer>
				<Sandboxing focusedSection={focusedSection} />
				<PairedUrlStructure focusedSection={focusedSection} />
				<AMPDrawer
					className="amp-other-settings"
					heading={<h3>
{__('Other', 'amp')}
</h3>}
					hiddenTitle={__('Other', 'amp')}
					id="other-settings"
					initialOpen={'other-settings' === focusedSection}
				>
					<MobileRedirection />
					{HAS_DEPENDENCY_SUPPORT && <DeveloperTools />}
					<ToggleUseNativeImgTag />
					<DeleteDataAtUninstall />
				</AMPDrawer>
				<SettingsFooter />
			</form>
			<UnsavedChangesWarning
				excludeUserContext={true}
				appRoot={appRoot}
			/>
		</>
	);
}
Root.propTypes = {
	appRoot: PropTypes.instanceOf(global.Element),
};

domReady(() => {
	const root = document.getElementById('amp-settings-root');

	if (!root) {
		return;
	}

	errorHandler = (event) => {
		// Handle only own errors.
		if (event.filename && /amp-settings(\.min)?\.js/.test(event.filename)) {
			render(<ErrorScreen error={event.error} />, root);
		}
	};

	global.addEventListener('error', errorHandler);

	render(
		<Providers>
			<Root appRoot={root} />
		</Providers>,
		root
	);
});
