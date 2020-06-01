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
		slug: 'site-scan',
		title: __( 'Site scan', 'amp' ),
		PageComponent: SiteScan,
	},
	{
		slug: 'technical-background',
		title: __( 'Technical background', 'amp' ),
		PageComponent: TechnicalBackground,
	},
	{
		slug: 'site-goals',
		title: __( 'Site goals', 'amp' ),
		PageComponent: Goals,
	},
	{
		slug: 'template-modes',
		title: __( 'Template modes', 'amp' ),
		PageComponent: TemplateMode,
	},
	{
		slug: 'reader-themes',
		title: __( 'Reader themes', 'amp' ),
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
