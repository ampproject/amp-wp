/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { AMPSettingToggle } from '../components/amp-setting-toggle';
import { Options } from '../components/options-context-provider';
import { Loading } from '../components/loading';

/**
 * Data deletion at uninstallation toggle on the settings page.
 */
export function DeleteDataAtUninstall() {
	const { editedOptions, fetchingOptions, updateOptions } = useContext( Options );

	if ( fetchingOptions ) {
		return <Loading />;
	}

	const deleteDataAtUninstall = editedOptions?.delete_data_at_uninstall;
	return (
		<section className="delete-data-at-uninstall">
			<AMPSettingToggle
				checked={ true === deleteDataAtUninstall }
				title={ __( 'Delete plugin data at uninstall', 'amp' ) }
				onChange={ () => {
					updateOptions( { delete_data_at_uninstall: ! deleteDataAtUninstall } );
				} }
			/>
			<p>
				{ __( 'When you uninstall the plugin you have the choice of whether its data should also be deleted. Examples of plugin data include the settings, validated URLs, and transients used to store image dimensions and parsed stylesheets.', 'amp' ) }
			</p>
		</section>
	);
}
