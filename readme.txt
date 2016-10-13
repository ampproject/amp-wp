=== AMP ===
Contributors: batmoo, joen, automattic, potatomaster
Tags: amp, mobile
Requires at least: 4.4
Tested up to: 4.6
Stable tag: 0.4.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enable Accelerated Mobile Pages (AMP) on your WordPress site.

== Description ==

This plugin adds support for the [Accelerated Mobile Pages](https://www.ampproject.org) (AMP) Project, which is an an open source initiative that aims to provide mobile optimized content that can load instantly everywhere.

With the plugin active, all posts on your site will have dynamically generated AMP-compatible versions, accessible by appending `/amp/` to the end your post URLs. For example, if your post URL is `http://example.com/2016/01/01/amp-on/`, you can access the AMP version at `http://example.com/2016/01/01/amp-on/amp/`. If you do not have [pretty permalinks](https://codex.wordpress.org/Using_Permalinks#mod_rewrite:_.22Pretty_Permalinks.22) enabled, you can do the same thing by appending `?amp=1`, i.e. `http://example.com/2016/01/01/amp-on/?amp=1`

Note #1: that Pages and archives are not currently supported. Pages support is being worked on.

Note #2: this plugin only creates AMP content but does not automatically display it to your users when they visit from a mobile device. That is handled by AMP consumers such as Google Search. For more details, see the [AMP Project FAQ](https://www.ampproject.org/docs/support/faqs.html).

Follow along with or contribute to the development of this plugin at https://github.com/Automattic/amp-wp

== Installation ==

1. Upload the folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. You may need to refresh your permalinks by going to `Settings > Permalinks` and tapping the `Save` button. 

== Frequently Asked Questions ==

= How do I customize the AMP output for my site? =

You can tweak a few things like colours from the AMP Customizer. From the Dashboard, go to `Appearance > AMP`.

For deeper level customizations, please see the readme at https://github.com/Automattic/amp-wp/blob/master/readme.md

= What about ads and shortcodes and such? =

Check out https://github.com/Automattic/amp-wp/blob/master/readme.md#handling-media

= What about analytics? =

Many plugins are adding AMP support already. If you handling analytics yourself, please see https://github.com/Automattic/amp-wp/blob/master/readme.md#analytics

= Google Webmaster Tools is reporting validation errors for my site. How do I fix them? =

The best place to start is to open a new discussion in the [support forum](https://wordpress.org/support/plugin/amp) with details on what the specific validation error is.

= Why aren't Pages supported yet =

A wise green Yoda once said, "Patience you must have, my young padawan." We're working on it :)

== Changelog ==

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
- AMP Customizer: Pick your colours and make the template your own (props DrewAPicture and 10up)
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

```
if ( function_exists( 'amp_backcompat_use_v03_templates' ) ) {
    amp_backcompat_use_v03_templates();
}
```

For more details, please see https://wordpress.org/support/topic/v0-4-whats-new-and-possible-breaking-changes/

= 0.3.1 =

* Breaking change: `AMP_QUERY_VAR` is now defined right before `amp_init`.
* Breaking change: class names for elements in the default template were prefixed with `amp-wp-`. Any styles targeting these classes should be updated.

= 0.3 =

* Breaking change: `style.css` no longer contains the `<style> tag. If you have a custom stylesheet, you need to update it to remove the tag.
* Breaking change: `single.php` no longer includes the AMP boilerplate styles. They are instead added via the `amp_post_template_head` hook. If you have a custom template, please remove the boilerplate styles.
