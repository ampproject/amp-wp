=== AMP ===
Contributors: google, xwp, automattic, westonruter, swissspidy, stubgo, ryankienstra, albertomedina, tweetythierry
Tags: amp, framework, components, performance, mobile, stories
Requires at least: 4.9
Tested up to: 5.2
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.4

Enable AMP on your WordPress site, the WordPress way.

== Description ==

The [AMP Project](http://amp.dev) is an open-source initiative aiming to make the web better for all. AMP enables web experiences that are consistently fast, beautiful and high-performing across distribution platforms. The official AMP plugin for WordPress supports fully integrated AMP publishing for WordPress sites, with robust capabilities and granular publisher controls.

Features and capabilities provided by the plugin include:

- **AMP-first Experiences**: enabling [full-site AMP experiences](https://amp.dev/about/websites) without sacrificing the flexibility of the platform or the fidelity of content.
- **Core Theme Support**: enabling AMP compatibility for all core themes, from Twenty Ten all the way through Twenty Nineteen.
- **Compatibility Tool**: when automatic conversion of markup to AMP is not possible, debug AMP validation errors with detailed information including the invalid markup and the specific components responsible on site (e.g theme, plugin, embed); validation errors are shown contextually with their respective blocks in the editor.
- **CSS Tree Shaking**: automatically remove the majority of unused CSS to bring the total under AMP's 50KB limit; when the total after tree shaking is still over this limit, prioritization is used so that the all-important theme stylesheet important is retained, leaving less important ones to be excluded (e.g. print styles).
- ✨ **AMP Stories** (beta): the AMP plugin enables the creation, editing, and publishing of [AMP Stories](https://amp.dev/about/stories) in WordPress; leverage the magic of storytelling the WordPress way!

The plugin can be configured to follow one of three different template modes: Standard, Transitional, and Reader. In Standard mode you use AMP as the framework for your site, and there need not be any separate AMP and non-AMP versions. When configured to operate in Reader and Transitional modes, a given page will have a canonical URL as well as a corresponding (paired) AMP URL. The AMP plugin is not serving as a mobile theme; it does not redirect mobile devices to the AMP version. Instead, the AMP version is served to mobile visitors when they find the content on platforms such as Twitter, Pinterest, Google Search, and others. Reader mode only supports serving AMP for singular posts, pages, and other post types, whereas Standard and Transitional mode support serving the entire site as AMP.

With the official AMP plugin for WordPress, the WordPress ecosystem is provided with the capabilities and tools it needs to build world-class AMP experiences without deviating from its standard, flexible, and well-known content creation workflow.

== Installation ==

1. Upload the folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. If you currently use older versions of the plugin in `Reader` mode, it is strongly encouraged to migrate to `Transitional` or `Standard` mode. Depending on your theme/plugins, some development work may be required.

== Getting Started ==

To learn more about the plugin and start leveraging its capabilities to power your AMP content creation workflow check [the official AMP plugin product site](https://amp-wp.org).

If you are a developer, we encourage you to [follow along](https://github.com/ampproject/amp-wp) or [contribute](https://github.com/ampproject/amp-wp/blob/develop/contributing.md) to the development of this plugin on GitHub.

== Screenshots ==

1. Create great web experiences via AMP-powered websites or visually rich, engaging stories.
2. Story editor enables creation of pages in a horizontal, page-based interface, with background media, with blocks that can be dragged, rotated, and animated.
3. In the website experience, theme support enables you to reuse the active theme's templates and stylesheets; all WordPress features (menus, widgets, comments) are available in AMP.
4. All core themes are supported, but many themes can be served as AMP with minimal changes, Otherwise, behavior is often as if JavaScript is turned off in the browser since scripts are removed.
5. Reader mode templates are still available, but they are differ from the active theme, any validation errors are silently sanitized.
6. Switch from Reader mode to Transitional or Standard mode in AMP settings screen. You may need to disable the admin bar in AMP if your theme has a larger amount of CSS.
7. Make the entire site available in AMP or pick specific post types and templates; you can also opt-out on per-post basis.
8. Plugin checks for AMP validity and will indicate when either: no issues are found, new issues need moderation, or issues block AMP from being served.
9. The editor will surface validation issues during content authoring. The specific blocks with validation errors are indicated.
10. Validated URLs include the list of validation errors encountered, giving control over whether sanitization for a validation error is accepted or rejected.
11. Styles added by themes and plugins are automatically concatenated, minified, and tree-shaken to try to keep the total under 50KB of inline CSS.
12. A WP-CLI command is provided to check the URLs on a site for AMP validity. Results are available in the admin for inspection.

== Changelog ==

For the plugin’s changelog, please see [the Releases page on GitHub](https://github.com/ampproject/amp-wp/releases).
