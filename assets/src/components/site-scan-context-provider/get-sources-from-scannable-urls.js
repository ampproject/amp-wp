/**
 * Internal dependencies
 */
import { getPluginSlugFromFile } from '../../common/helpers/get-plugin-slug-from-file';

/**
 * Process a plugin source and add it to the plugins map.
 *
 * @param {Object} source          The source object.
 * @param {Object} validationError The validation error object.
 * @param {Map}    plugins         The plugins map to update.
 * @param {string} targetUrl       The URL to add to the plugin's URL set.
 */
function processPluginSource(source, validationError, plugins, targetUrl) {
	const pluginSlug = getPluginSlugFromFile(source.name);

	if ('gutenberg' === pluginSlug && validationError.sources.length > 1) {
		return;
	}

	plugins.set(
		pluginSlug,
		new Set([...(plugins.get(pluginSlug) || []), targetUrl])
	);
}

/**
 * Process a theme source and add it to the themes map.
 *
 * @param {Object} source    The source object.
 * @param {Map}    themes    The themes map to update.
 * @param {string} targetUrl The URL to add to the theme's URL set.
 */
function processThemeSource(source, themes, targetUrl) {
	themes.set(
		source.name,
		new Set([...(themes.get(source.name) || []), targetUrl])
	);
}

/**
 * Process validation error sources and update plugins and themes maps.
 *
 * @param {Object} validationError The validation error object.
 * @param {Map}    plugins         The plugins map to update.
 * @param {Map}    themes          The themes map to update.
 * @param {string} targetUrl       The URL to add to source URL sets.
 */
function processValidationErrorSources(
	validationError,
	plugins,
	themes,
	targetUrl
) {
	if (!validationError?.sources?.length) {
		return;
	}

	for (const source of validationError.sources) {
		if (!source?.type) {
			continue;
		}

		if (source.type === 'plugin') {
			processPluginSource(source, validationError, plugins, targetUrl);
		} else if (source.type === 'theme') {
			processThemeSource(source, themes, targetUrl);
		}
	}
}

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

		const targetUrl = useAmpUrls ? ampUrl : url;

		for (const validationError of validationErrors) {
			processValidationErrorSources(
				validationError,
				plugins,
				themes,
				targetUrl
			);
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
