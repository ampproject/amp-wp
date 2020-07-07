
/**
 * WordPress dependencies
 */
import { useContext, useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Options } from '../components/options-context-provider';
import { Selectable } from '../components/selectable';

/**
 * Plugin suppression section of the settings page.
 */
export function PluginSuppression() {
	const { fetchingOptions } = useContext( Options );

	const [ supportedTemplatesTableFound, setSupportedTemplatesTableFound ] = useState( false );

	const pluginSuppressionContainer = useRef();

	/**
	 * Pull the PHP-generated plugin suppression section into the JS-generated area.
	 */
	useEffect( () => {
		const settingsSections = [ ...document.querySelectorAll( '#amp-settings-sections > table' ) ];
		const supportedTemplatesTable = settingsSections.find( ( section ) => section.querySelector( '.amp-suppressed-plugins' ) );
		if ( supportedTemplatesTable ) {
			pluginSuppressionContainer.current.appendChild( supportedTemplatesTable );
			setSupportedTemplatesTableFound( true );
		}
	}, [] );

	return (
		<section className={ fetchingOptions || ! supportedTemplatesTableFound ? 'hidden' : '' }>
			<h2>
				{ __( 'Plugin Suppression', 'amp' ) }
			</h2>
			<Selectable className="plugin-suppression">
				<div ref={ pluginSuppressionContainer } />
			</Selectable>
		</section>
	);
}
