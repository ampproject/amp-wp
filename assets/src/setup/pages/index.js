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
import { Summary } from './summary';
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
		slug: 'summary',
		title: __( 'Summary', 'amp' ),
		PageComponent: Summary,
	},
	{
		slug: 'done',
		title: __( 'Done', 'amp' ),
		PageComponent: Save,
	},
];

