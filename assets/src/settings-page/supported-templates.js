/**
 * External dependencies
 */
import PropTypes from 'prop-types';

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
import { SupportedTemplatesVisibility } from './supported-templates-visibility';

/**
 * Supported templates section of the settings page.
 *
 * @param {Object} props Component props.
 * @param {Object} props.themeSupportArgs Theme support settings passed from the backend.
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

	return (
		<section className={ ( 'reader' === themeSupport && 'legacy' === readerTheme ) || ! themeSupport ? 'hidden' : '' }>
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
