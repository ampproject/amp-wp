/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { TechnicalBackground } from './technical-background';
import { Goals } from './goals';
import { TemplateMode } from './template-mode';
import { ChooseReaderTheme } from './choose-reader-theme';
import { SiteConfigurationSummary } from './site-configuration-summary';
import { Save } from './save';
import { SiteScan } from './site-scan';

/**
 * Settings for the pages in the application.
 */
export const PAGES = [
	{
		title: __( 'Site Scan', 'amp' ),
		PageComponent: SiteScan,
	},
	{
		title: __( 'Technical background', 'amp' ),
		PageComponent: TechnicalBackground,
	},
	{
		title: __( 'Site goals', 'amp' ),
		PageComponent: Goals,
	},
	{
		title: __( 'Template modes', 'amp' ),
		PageComponent: TemplateMode,
	},
	{
		title: __( 'Reader themes', 'amp' ),
		PageComponent: ChooseReaderTheme,
	},
	{
		title: __( 'Site configuration', 'amp' ),
		PageComponent: SiteConfigurationSummary,
	},
	{
		title: __( 'Done', 'amp' ),
		PageComponent: Save,
	},
];
