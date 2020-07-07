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
export function SupportedTemplates( { themeSupportArgs } ) {
	const { editedOptions } = useContext( Options );

	const { reader_theme: readerTheme, theme_support: themeSupport } = editedOptions;

	const supportedTemplatesContainer = useRef();

	useEffect( () => {
		const settingsSections = [ ...document.querySelectorAll( '#amp-settings-sections > table' ) ];
		const supportedTemplatesTable = settingsSections.find( ( section ) => section.querySelector( '.amp-template-support-field' ) );
		supportedTemplatesContainer.current.appendChild( supportedTemplatesTable );
	}, [] );

	return (
		<section className={ 'legacy' === readerTheme || ! themeSupport ? 'hidden' : '' }>
			<h2>
				{ __( 'Supported Templates', 'amp' ) }
			</h2>
			<Selectable className="supported-templates">
				<SupportedTemplatesToggle themeSupportArgs={ themeSupportArgs } />
				<SupportedTemplatesVisibility />
				<div ref={ supportedTemplatesContainer } />
			</Selectable>
		</section>
	);
}

SupportedTemplates.propTypes = {
	themeSupportArgs: PropTypes.oneOfType( [
		PropTypes.bool,
		PropTypes.shape( {
			templates_supported: PropTypes.any,
		} ),
	] ).isRequired,
};
