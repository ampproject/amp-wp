/**
 * WordPress dependencies
 */
import { createInterpolateElement, useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { AMPSettingToggle } from '../components/amp-setting-toggle';
import { Options } from '../components/options-context-provider';
import { Loading } from '../components/loading';

/**
 * Use native img tag instead of amp-img tag toggle on the settings page.
 */
export function ToggleUseNativeImgTag() {
	const { editedOptions, fetchingOptions, updateOptions } = useContext( Options );

	if ( fetchingOptions ) {
		return <Loading />;
	}

	const useNativeImgTag = editedOptions?.use_native_img_tag;

	return (
		<section className="use-native-img-tag">
			<AMPSettingToggle
				checked={ true === useNativeImgTag }
				title={ __( 'Use native img tag', 'amp' ) }
				onChange={ () => {
					updateOptions( { use_native_img_tag: ! useNativeImgTag } );
				} }
			/>
			<p>
				{
					createInterpolateElement(
						__( 'By enabling this, The AMP plugin will use native <ImgTag /> instead of <AMPImgTag />.', 'amp' ),
						{
							ImgTag: (
								<code>
									{ '<img>' }
								</code>
							),
							AMPImgTag: (
								<code>
									{ '<amp-img>' }
								</code>
							),
						},
					)
				}
			</p>
		</section>
	);
}
