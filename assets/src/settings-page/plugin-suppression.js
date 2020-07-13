/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Options } from '../components/options-context-provider';

export function PluginRow( pluginDetails ) {
	console.log( pluginDetails );

	return null;
}

export function PluginSuppression() {
	const { editedOptions, fetchingOptions } = useContext( Options );

	const { suppressible_plugins: suppressiblePlugins } = editedOptions;

	console.log( editedOptions );
	return (
		<>
			<p>
				{ __( 'When a plugin adds markup which is invalid on AMP pages, you have two options: you can review the validation error, determine that the invalid markup is not needed, and let the AMP plugin remove it. Alternatively, you can suppress the offending plugin from running on AMP pages. Below is the list of active plugins which have caused validation issues.', 'amp' ) }
			</p>
			<table id="suppressed-plugins-table" className="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th className="column-status" scope="col">
							{ __( 'Status', 'amp' ) }
						</th>
						<th className="column-plugin" scope="col">
							{ __( 'Plugin', 'amp' ) }
						</th>
						<th className="column-details" scope="col">
							{ __( 'Details', 'amp' ) }
						</th>
					</tr>
				</thead>
				<tbody>
					{ Object.keys( suppressiblePlugins ).map( ( pluginKey ) => (
						<PluginRow key={ `plugin-row-${ pluginKey }` } pluginDetails={ suppressiblePlugins[ pluginKey ] } />
					) ) }
				</tbody>
			</table>

		</>
	);
}
