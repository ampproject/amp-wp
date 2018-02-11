=== AMP for WordPress ===
Contributors: batmoo, joen, automattic, potatomaster, albertomedina, google, xwp, westonruter
Tags: amp, mobile
Requires at least: 4.7
Tested up to: 4.9
Stable tag: 0.6.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.2

Enable Accelerated Mobile Pages (AMP) on your WordPress site.

== Description ==

This plugin adds support for the [Accelerated Mobile Pages](https://www.ampproject.org) (AMP) Project, which is an open source initiative that aims to provide mobile optimized content that can load instantly everywhere.

With the plugin active, all posts on your site will have dynamically generated AMP-compatible versions, accessible by appending `/amp/` to the end your post URLs. For example, if your post URL is `http://example.com/2016/01/01/amp-on/`, you can access the AMP version at `http://example.com/2016/01/01/amp-on/amp/`. If you do not have [pretty permalinks](https://codex.wordpress.org/Using_Permalinks#mod_rewrite:_.22Pretty_Permalinks.22) enabled, you can do the same thing by appending `?amp=1`, i.e. `http://example.com/?p=123&amp=1`

Note #1: homepage, the blog index, and archives are not currently supported.

Note #2: this plugin only creates AMP content but does not automatically display it to your users when they visit from a mobile device. That is handled by AMP consumers such as Google Search. For more details, see the [AMP Project FAQ](https://www.ampproject.org/docs/support/faqs.html).

Follow along with or [contribute](https://github.com/Automattic/amp-wp/blob/develop/contributing.md) to the development of this plugin [on GitHub](https://github.com/Automattic/amp-wp). For more information on the plugin, how the plugin works and how to configure and extend it, please see the [project wiki](https://github.com/Automattic/amp-wp/wiki).

== Screenshots ==

1. Post rendered in AMP template.
1. Customizing appearance of AMP template.
1. Article from New York Post showing customized AMP template.
1. Article from TNW showing customized AMP template.
1. Article from Halfbrick showing customized AMP template.

== Installation ==

1. Upload the folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You may need to refresh your permalinks by going to `Settings > Permalinks` and tapping the `Save` button.

== Changelog ==

= 0.6.2 (2018-02-12) =

- Reduce frequency of flushing rewrite rules and harden, use escaped translation functions, and make minor changes to improve logic/style. See [#953](https://github.com/Automattic/amp-wp/pull/953). Props philipjohn, westonruter.
- Fix AMP preview icon in Firefox. See [#920](https://github.com/Automattic/amp-wp/pull/920). Props zigasancin.

= 0.6.1 (2018-02-09) =

Bump version to re-release to ensure temporarily-broken 0.6.0 ZIP build is permanently fixed, without requiring a site to re-install the plugin.

= 0.6.0 (2018-01-23) =

- Add support for the "page" post type. A new `page.php` is introduced with template parts factored out (`html-start.php`, `header.php`, `footer.php`, `html-end.php`) and re-used from `single.php`. Note that AMP URLs will end in `?amp` instead of `/amp/`. See [#825](https://github.com/Automattic/amp-wp/pull/825). Props technosailor, ThierryA, westonruter.
- Add AMP post preview button alongside non-AMP preview button. See [#813](https://github.com/Automattic/amp-wp/pull/813). Props ThierryA, westonruter.
- Add ability to disable AMP on a per-post basis via toggle in publish metabox. See [#813](https://github.com/Automattic/amp-wp/pull/813). Props ThierryA, westonruter.
- Add AMP settings admin screen for managing which post types have AMP support, eliminating the requirement to add `add_post_type_support()` calls in theme or plugin. See [#811](https://github.com/Automattic/amp-wp/pull/811). Props ThierryA, westonruter.
- Add generator meta tag for AMP. See [#810](https://github.com/Automattic/amp-wp/pull/810). Props vaporwavre.
- Add code quality checking via phpcs, eslint, jscs, and jshint. See [#795](https://github.com/Automattic/amp-wp/pull/795). Props westonruter.
- Add autoloader to reduce complexity. See [#828](https://github.com/Automattic/amp-wp/pull/828). Props mikeschinkel, westonruter, ThierryA.
- Fix Polldaddy amd SoundCloud embeds. Add vanilla WordPress "embed" test page. A new `bin/create-embed-test-post.php` wp-cli script is introduced. See [#829](https://github.com/Automattic/amp-wp/pull/829). Props kienstra, westonruter, ThierryA.
- Merge AMP Customizer into main Customizer. See [#819](https://github.com/Automattic/amp-wp/pull/819). Props kaitnyl, westonruter.
- Update AMP HTML tags and attributes. A new `bin/amphtml-update.sh` bash script is introduced. Fixes Playbuzz. See [#823](https://github.com/Automattic/amp-wp/pull/823). Props kienstra, ThierryA, westonruter.
- Remove erroneous hash from id on amp-wp-header. See [#853](https://github.com/Automattic/amp-wp/pull/853). Props eshannon3.

See [0.6 milestone](https://github.com/Automattic/amp-wp/milestone/5?closed=1).

= 0.5.1 (2017-08-17) =

- Fix: issues with invalid tags not being stripped out (e.g. script tags) (h/t tmmbecker, fahmi182, pppdog, seejacobscott, RavanH, jenniejj, lkraav, simonrperry for the reports).
- Fix: issues with dimension extraction for protocol-less and relative URLs (h/t ktmn for the report).

= 0.5 (2017-08-04) =

- Whitelist Sanitizer: Replace Blacklist Sanitizer with a whitelist-based approach using the AMP spec (props delputnam)
- Image Dimensions: Replace fastimage with fasterimage for PHP 5.4+. Enables faster downloads and wider support (props gititon)
- Embed Handlers: Added support for Vimeo, SoundCloud, Pinterest (props amedina) and PlayBuzz (props lysk88)
- Analytics: UI for easier addition of analytics tags (props amedina)
- Fix: parse query strings properly (props amyevans)
- Fix: Old slug redirect for AMP URLs (props rahulsprajapati)
- Fix: Handle issues with data uri images in CSS (props trepmal)
- Fix: Add amp-video js for amp-video tags (props ptbello)
- Fix: Output CSS for feature image (props mjangda)
- Fix: Fix attribute when adding AMP Mustache lib (props luigitec)
- Fix: Various documentation updates (props piersb, bhhaskin)
- Fix: PHP Warnings from `register_customizer_ui` (props jahvi)
- Fix: Coding Standards (props paulschreiber)

= 0.4.2 (2016-10-13) =

- Fix: Prevent validation errors for `html` tag (h/t Maxime2 and everyone else that reported this error)
- Fix: Handle variable name conflict that was causing content_max_width to be ignored (h/t mimancillas)
- Fix: Prevent errors when nodes don't have attributes (h/t stephenmax)
- Fix: Back-compat for 4.5 (add sanitize_hex_color function, h/t xotihcan)
- Fix: Handle gif featured images (h/t protocolil)
- Documentation updates (props troyxmccall)

= 0.4.1 (2016-10-10) =

- Fix: Don't fire the_content for featured image output
- Fix: Don't show comment link when disabled and no comments on post (h/t neotrope)
- Fix: strip `!important` from inline styles (h/t compointdesigner and enriccardonagmailcom)

= 0.4 (2016-10-06) =

- New template: spiffy, shiny, and has the fresh theme smell (props allancole and the Automattic Theme Team).
- *Warning*: The template update has potential breaking changes. Please see https://wordpress.org/support/topic/v0-4-whats-new-and-possible-breaking-changes/
- AMP Customizer: Pick your colors and make the template your own (props DrewAPicture and 10up)
- Fix: support for inline styles (props coreymckrill).
- Fix: no more fatal errors when tags not supported by post type (props david-binda)
- Fix: no more unnecessary `<br>` tags.
- Fix: sanitize children of removed nodes (like empty `<a>` tags) (props Maxime2).
- Fix: no more broken YouTube URLs with multiple ?s.
- Fix: properly handle tel and sms schemes (h/t soundstrategies).
- Fix: remove amp endpoint on deactivate.
- New filter: `amp_pre_get_permalink` if you want a completely custom AMP permalink.

= 0.3.3 (Aug 18, 2016) =

- Handle many more validation errors (props bcampeau and alleyinteractive).
- New filter: `amp_post_template_dir` (props mustafauysal).
- New template: Nav bar is now it's own template part (props jdevalk).
- Better ratio for YouTube embeds.
- Fix: better timezone handling (props rinatkhaziev).
- Fix: better handling of non-int dimensions (like `100%`).
- Fix: better handling of empty dimensions.
- Fix: `autoplay` is a bool-like value.
- Fix: breakage when using the `query_string` hook (h/t mkuplens).
- Fix: don't break really large Twitter IDs.
- Fix: don't break Instagram shortcodes when using URLs with querystrings.
- Readme improvements (props nickjohnford, sotayamashita)

= 0.3.2 (Mar 4, 2016) =

* Jetpack Stats support.
* Better version of Merriweather and use system fonts for sans-serif (props mattmiklic).
* Move font to stylesheet so it can be more easily overridden (props mattmiklic).
* Fix: Template loading issues on Windows. (Thanks to everyone who reported this, especially w33zy for pointing out the `validate_file` issue.)
* Fix: don't run AMP on post comment feeds (props kraftbj).
* Fix: un-break pagination when using a static home page with multiple pages.
* Fix: force amp-iframe to use https to validate correctly (props mister-ben).
* Fix: validation for `target` and `video`/`audio` attributes.
* Fix: clipped images in galleries (thanks tobaco).

= 0.3.1 (Feb 24, 2016) =

* Allow custom query var (props vaurdan).
* Fix AMP URLs for non-pretty permalinks (props rakuishi).
* Fix for password-protected posts.
* Fix dimension extraction for schema-less or relative image URLs.
* Better fallback for images with no dimensions.
* Validation fixes for `a` tags (props kraftbj).
* Updated AMP boilerplate.
* Allow `on` tags for elements (props Steven Evatt).
* Prefixed class names.

= 0.3 (Feb 18, 2016) =

* Fetch dimensions for hotlinked images.
* Add amp-facebook support.
* Add some new actions and filters (e.g. `amp_init`).
* Fix validation errors for [gallery] shortcodes.
* Fix issues with path validation on Windows.
* Fix issues with really squeezed layout.
* Breaking change: `style.css` no longer contains the `<style> tag. If you have a custom stylesheet, you need to update it to remove the tag.
* Breaking change: `single.php` no longer includes the AMP boilerplate styles. They are instead added via the `amp_post_template_head` hook. If you have a custom template, please remove the boilerplate styles.

= 0.2 (Jan 28, 2016) =

* Lots and lots and lots of compatibility and validation fixes
* Lots and lots and lots of improvements for customization

= 0.1 =
* Initial version

== Upgrade Notice ==

= 0.4 =

* Breaking change: The new template has changes to markup, class names, and styles that may not work with existing customizations. If you want to stay on the old template for now, you can use the following code snippet:

<pre lang="php">
if ( function_exists( 'amp_backcompat_use_v03_templates' ) ) {
	amp_backcompat_use_v03_templates();
}
</pre>

For more details, please see https://wordpress.org/support/topic/v0-4-whats-new-and-possible-breaking-changes/

= 0.3.1 =

* Breaking change: `AMP_QUERY_VAR` is now defined right before `amp_init`.
* Breaking change: class names for elements in the default template were prefixed with `amp-wp-`. Any styles targeting these classes should be updated.

= 0.3 =

* Breaking change: `style.css` no longer contains the `<style> tag. If you have a custom stylesheet, you need to update it to remove the tag.
* Breaking change: `single.php` no longer includes the AMP boilerplate styles. They are instead added via the `amp_post_template_head` hook. If you have a custom template, please remove the boilerplate styles.
