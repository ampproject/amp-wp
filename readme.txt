=== AMP ===
Contributors: batmoo, joen, automattic
Tags: amp, mobile
Requires at least: 4.4
Tested up to: 4.4
Stable tag: 0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enable Accelerated Mobile Pages (AMP) on your WordPress site.

== Description ==

This plugin adds support for the [Accelerated Mobile Pages](https://www.ampproject.org) (AMP) Project, which is an an open source initiative that aims to provide mobile optimized content that can load instantly everywhere.

With the plugin active, all posts on your site will have dynamically generated AMP-compatible versions, accessible by appending `/amp/` to the end your post URLs. For example, if your post URL is `http://example.com/2016/01/01/amp-on/`, you can access the AMP version at `http://example.com/2016/01/01/amp-on/amp/`. If you do not have [pretty permalinks](https://codex.wordpress.org/Using_Permalinks#mod_rewrite:_.22Pretty_Permalinks.22) enabled, you can do the same thing by appending `?amp=1`, i.e. `http://example.com/2016/01/01/amp-on/?amp=1`

Note #1: that Pages and archives are not currently supported.

Note #2: this plugin only creates AMP content but does not automatically display it to your users when they visit from a mobile device. That is handled by AMP consumers such as Google Search. For more details, see the [AMP Project FAQ](https://www.ampproject.org/docs/support/faqs.html).

Follow along with or contribute to the development of this plugin at https://github.com/Automattic/amp-wp

== Installation ==

1. Upload the folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How do I customize the AMP output for my site? =

You can find details about customization options at https://github.com/Automattic/amp-wp/blob/master/readme.md

== Changelog ==

= 0.4 =

* Breaking change: class names for elements in the default template were prefixed with `amp-wp-`. Any styles targeting these classes should be updated.

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
