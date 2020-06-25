/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { TechnicalBackground } from './technical-background';
import { TemplateMode } from './template-mode';
import { ChooseReaderTheme } from './choose-reader-theme';
import { SiteConfigurationSummary } from './site-configuration-summary';
import { Save } from './save';
import { Welcome } from './welcome';

/**
 * Settings for the pages in the application.
 */
export const PAGES = [
	{
		slug: 'welcome',
		title: __( 'Welcome', 'amp' ),
		PageComponent: Welcome,
		showTitle: false,
	},
	{
		slug: 'technical-background',
		title: __( 'Technical background', 'amp' ),
		PageComponent: TechnicalBackground,
	},
	{
		slug: 'template-modes',
		title: __( 'Template modes', 'amp' ),
		PageComponent: TemplateMode,
	},
	{
		slug: 'theme-selection',
		title: __( 'Theme selection', 'amp' ),
		PageComponent: ChooseReaderTheme,
	},
	{
		slug: 'site-configuration',
		title: __( 'Site configuration', 'amp' ),
		PageComponent: SiteConfigurationSummary,
	},
	{
		slug: 'done',
		title: __( 'Done', 'amp' ),
		PageComponent: Save,
	},
];

