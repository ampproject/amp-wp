/**
 * Internal dependencies
 */
import { getPluginSlugFromFile } from '../../common/helpers/get-plugin-slug-from-file';

/**
 * From an array of scannable URLs, get plugin and theme slugs along with URLs for which AMP validation errors occur.
 *
 * See the corresponding PHP logic in `\AMP_Validated_URL_Post_Type::render_sources_column()`.
 *
 * @param {Array}   scannableUrls      Array of scannable URLs.
 * @param {Object}  options            Additional options object.
 * @param {boolean} options.useAmpUrls Whether to return `amp_url` instead of the regular URL in the lists of sources.
 * @return {Object} An object consisting of `plugins` and `themes` arrays.
 */
export function getSourcesFromScannableUrls(
	scannableUrls = [],
	{ useAmpUrls = false } = {}
) {
	const plugins = new Map();
	const themes = new Map();

	for (const scannableUrl of scannableUrls) {
		const {
			amp_url: ampUrl,
			url,
			validation_errors: validationErrors,
		} = scannableUrl;

		if (!validationErrors?.length) {
			continue;
		}

		for (const validationError of validationErrors) {
			if (!validationError?.sources?.length) {
				continue;
			}

			for (const source of validationError.sources) {
				if (source.type === 'plugin') {
					const pluginSlug = getPluginSlugFromFile(source.name);

					if (
						'gutenberg' === pluginSlug &&
						validationError.sources.length > 1
					) {
						continue;
					}

					plugins.set(
						pluginSlug,
						new Set([
							...(plugins.get(pluginSlug) || []),
							useAmpUrls ? ampUrl : url,
						])
					);
				} else if (source.type === 'theme') {
					themes.set(
						source.name,
						new Set([
							...(themes.get(source.name) || []),
							useAmpUrls ? ampUrl : url,
						])
					);
				}
			}
		}
	}

	// Skip including AMP in the summary, since AMP is like core.
	plugins.delete('amp');

	return {
		plugins: [...plugins].map(([slug, urls]) => ({
			slug,
			urls: [...urls],
		})),
		themes: [...themes].map(([slug, urls]) => ({ slug, urls: [...urls] })),
	};
}
