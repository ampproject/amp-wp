/**
 * WordPress dependencies
 */
import { useContext, useEffect, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { SupportedTemplatesToggle } from '../components/supported-templates-toggle';
import { Options } from '../components/options-context-provider';
import { Selectable } from '../components/selectable';
import { SupportedTemplatesVisibility } from './old-supported-templates-visibility';

/**
 * Supported templates section of the settings page.
 */
export function SupportedTemplates() {
	const { editedOptions } = useContext( Options );

	const { reader_theme: readerTheme, theme_support: themeSupport } = editedOptions;

	const supportedTemplatesContainer = useRef();

	/**
	 * Pull the PHP-generated form inputs into this React-generated section.
	 */
	useEffect( () => {
		const settingsSections = [ ...document.querySelectorAll( '#amp-settings-sections > table' ) ];
		const supportedTemplatesTable = settingsSections.find( ( section ) => section.querySelector( '.amp-template-support-field' ) );
		if ( supportedTemplatesTable ) {
			supportedTemplatesContainer.current.appendChild( supportedTemplatesTable );
		}
	}, [] );

	const isReader = 'reader' === themeSupport;

	return (
		<section className={ ! themeSupport || ( isReader && ! readerTheme ) ? 'hidden' : '' }>
			<h2>
				{ __( 'Supported Templates', 'amp' ) }
			</h2>
			<Selectable className="supported-templates">
				<SupportedTemplatesToggle />
				<SupportedTemplatesVisibility />
				<div ref={ supportedTemplatesContainer } />
			</Selectable>
		</section>
	);
}
