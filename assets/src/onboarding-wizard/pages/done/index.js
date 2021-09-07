/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useContext, useEffect, useState } from '@wordpress/element';

/**
 * External dependencies
 */
import { PREVIEW_URLS, SETTINGS_LINK } from 'amp-settings'; // From WP inline script.

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
import { User } from '../../../components/user-context-provider';
import { RadioGroup } from '../../../components/radio-group/radio-group';
import { Selectable } from '../../../components/selectable';
import { IconLaptopToggles } from '../../../components/svg/icon-laptop-toggles';
import { IconLaptopSearch } from '../../../components/svg/icon-laptop-search';
import { AMPSettingToggle } from '../../../components/amp-setting-toggle';
import { Preview } from './preview';
import { Saving } from './saving';

/**
 * Gets the title for the preview page selector.
 *
 * @param {string} page The page type.
 */
function getPreviewPageTitle( page ) {
	switch ( page ) {
		case 'home':
			return __( 'Homepage', 'amp' );

		case 'author':
			return __( 'Author page', 'amp' );

		case 'date':
			return __( 'Archive page', 'amp' );

		case 'search':
			return __( 'Search results', 'amp' );

		default:
			return `${ page.charAt( 0 ).toUpperCase() }${ page.slice( 1 ) }`;
	}
}

const previewPageOptions = Object.keys( PREVIEW_URLS ).map( ( page ) => ( {
	value: page,
	title: getPreviewPageTitle( page ),
} ) );

/**
 * Final screen, where data is saved.
 */
export function Done() {
	const {
		didSaveOptions,
		editedOptions: { theme_support: themeSupport, reader_theme: readerTheme },
		hasOptionsChanges,
		readerModeWasOverridden,
		saveOptions,
		savingOptions,
	} = useContext( Options );
	const { didSaveDeveloperToolsOption, saveDeveloperToolsOption, savingDeveloperToolsOption } = useContext( User );
	const { canGoForward, setCanGoForward } = useContext( Navigation );
	const { downloadedTheme, downloadingTheme, downloadingThemeError } = useContext( ReaderThemes );
	const [ isPreviewingAMP, setIsPreviewingAMP ] = useState( themeSupport !== 'standard' );
	const [ previewPageType, setPreviewPageType ] = useState( Object.keys( PREVIEW_URLS )[ 0 ] );

	/**
	 * Allow the finish button to be enabled.
	 */
	useEffect(
		() => {
			if ( ! canGoForward ) {
				setCanGoForward( true );
			}
		},
		[ setCanGoForward, canGoForward ],
	);

	/**
	 * Triggers saving of options on arrival to this screen.
	 */
	useEffect( () => {
		if ( ! didSaveOptions && ! savingOptions ) {
			saveOptions();
		}
	}, [ didSaveOptions, saveOptions, savingOptions ] );

	/**
	 * Triggers saving of user options on arrival of this screen.
	 */
	useEffect( () => {
		if ( ! didSaveDeveloperToolsOption && ! savingDeveloperToolsOption ) {
			saveDeveloperToolsOption();
		}
	}, [ didSaveDeveloperToolsOption, savingDeveloperToolsOption, saveDeveloperToolsOption ] );

	if ( savingOptions || savingDeveloperToolsOption || downloadingTheme || hasOptionsChanges ) {
		return <Saving />;
	}

	return (
		<div className="done">
			<h1 className="done__heading">
				{ __( 'Done', 'amp' ) }
			</h1>
			<div className="done__content done__content--primary">
				<h2 className="done__icon-title">
					<IconLaptopSearch />
					{ __( 'Review', 'amp' ) }
				</h2>
				{ 'reader' === themeSupport && downloadedTheme === readerTheme && (
					<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_SUCCESS }>
						{ __( 'Your Reader theme was automatically installed', 'amp' ) }
					</AMPNotice>
				) }
				{ readerModeWasOverridden && (
					<AMPNotice type={ NOTICE_TYPE_INFO } size={ NOTICE_SIZE_LARGE }>
						{ __( 'Because you selected a Reader theme that is the same as your site\'s active theme, your site has automatically been switched to Transitional template mode.', 'amp' ) }
					</AMPNotice>
				) }
				<p>
					{ __( 'Your site is ready to bring great experiences to your users!', 'amp' ) }
				</p>
				{ 'standard' === themeSupport && (
					<p>
						{ __( 'In Standard mode there is a single AMP version of your site. Browse your site below to ensure it meets your expectations.', 'amp' ) }
					</p>
				) }
				{ 'transitional' === themeSupport && (
					<>
						<p>
							{ __( 'In Transitional mode AMP and non-AMP versions of your site are served using your currently active theme.', 'amp' ) }
						</p>
						<p>
							{ __( 'Browse your site below to ensure it meets your expectations, and toggle the AMP setting to compare both versions.', 'amp' ) }
						</p>
					</>
				) }
				{ 'reader' === themeSupport && (
					<>
						<p>
							{ __( 'In Reader mode AMP is served using your selected Reader theme, and pages for your non-AMP site are served using your primary theme. Browse your site below to ensure it meets your expectations, and toggle the AMP setting to compare both versions.', 'amp' ) }
						</p>
						<p>
							{ __( 'As a last step, use the Customizer to tailor the Reader theme as needed.', 'amp' ) }
						</p>
					</>
				) }
				<Selectable className="done__links-container">
					<RadioGroup
						options={ previewPageOptions }
						selected={ previewPageType }
						onChange={ setPreviewPageType }
					/>
				</Selectable>
			</div>
			<div className="done__preview-container">
				{ 'reader' === themeSupport && downloadingThemeError && (
					<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
						{ __( 'There was an error downloading your Reader theme. As a result, your site is currently using the legacy reader theme. Please install your chosen theme manually.', 'amp' ) }
					</AMPNotice>
				) }
				{ 'standard' !== themeSupport && (
					<AMPSettingToggle
						text={ __( 'AMP', 'amp' ) }
						checked={ isPreviewingAMP }
						onChange={ () => setIsPreviewingAMP( ( mode ) => ! mode ) }
						compact={ true }
					/>
				) }
				<Preview url={ PREVIEW_URLS[ previewPageType ][ isPreviewingAMP ? 'amp_url' : 'url' ] } />
			</div>
			<div className="done__content done__content--secondary">
				<h2 className="done__icon-title">
					<IconLaptopToggles />
					{ __( 'Need help?', 'amp' ) }
				</h2>
				<ul className="done__list">
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
							/* translators: placeholder is a link to the settings page. */
							__( 'Try a different template mode <a href="%s" target="_blank" rel="noreferrer noopener">in settings</a>', 'amp' ),
							SETTINGS_LINK,
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
			</div>
		</div>
	);
}
