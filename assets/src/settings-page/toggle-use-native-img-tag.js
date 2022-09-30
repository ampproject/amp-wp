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
	const { editedOptions, fetchingOptions, updateOptions } =
		useContext(Options);

	if (fetchingOptions) {
		return <Loading />;
	}

	const useNativeImgTag = editedOptions?.use_native_img_tag;

	return (
		<section className="use-native-img-tag">
			<AMPSettingToggle
				checked={true === useNativeImgTag}
				title={__('Use native HTML image tag', 'amp')}
				onChange={() => {
					updateOptions({ use_native_img_tag: !useNativeImgTag });
				}}
			/>
			<p>
				{createInterpolateElement(
					__(
						'The native <ImgTag /> HTML can now be used instead of the AMP-specific <AMPImgTag /> tag. AMP no longer requires the latter because lazy-loading is now a feature of the Web platform. Using native images can further improve page performance, although not all AMP components have been updated to work with them (see <a>example</a>). If you have CSS that is specifically targeting <AMPImgTag /> tags, you will need to update them to target <ImgTag /> instead (which can be done by default since such tags are automatically rewritten in CSS selectors when conversions are done).',
						'amp'
					),
					{
						ImgTag: <code>
{'<img>'}
</code>,
						AMPImgTag: <code>
{'<amp-img>'}
</code>,
						a: (
							// eslint-disable-next-line jsx-a11y/anchor-has-content -- Anchor has content defined in the translated string.
							<a
								href="https://github.com/ampproject/amphtml/pull/38028"
								target="_blank"
								rel="noreferrer noopener"
							/>
						),
					}
				)}
			</p>
		</section>
	);
}
