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
 * Developer tools section of the settings page.
 */
export function DataRemoval() {
	const { editedOptions, fetchingOptions, updateOptions } = useContext( Options );

	const keepAmpData = editedOptions?.keep_amp_data;

	if ( fetchingOptions ) {
		return <Loading />;
	}

	return (
		<section className="developer-tools">
			<AMPSettingToggle
				checked={ true === keepAmpData }
				title={ __( 'Keep my data', 'amp' ) }
				onChange={ () => {
					updateOptions( { keep_amp_data: ! keepAmpData } );
				} }
			/>
			<p>
				{ __( 'When you delete the plugin you have the choice to have the data remain or removed.', 'amp' ) }
			</p>
		</section>
	);
}
