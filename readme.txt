=== AMP ===
Contributors: batmoo, joen, automattic
Tags: amp, mobile
Requires at least: 4.3
Tested up to: 4.3
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enable [Accelerated Mobile Pages](https://www.ampproject.org) on your WordPress site.

== Description ==

This plugin adds support for the Accelerated Mobile Pages (AMP) Project, which is an an open source initiative that aims to provide mobile optimized content that can load instantly everywhere.

With the plugin active, all content on your site will have dynamically generated AMP-compatible versions, accessible by appending `/amp/` to the end your permalinks. (If you do not have pretty permalinks enabled, you can do the same thing by appending `?amp=1`.)

Follow along with or contribute to the development of this plugin at https://github.com/Automattic/amp-wp

Developers: please note that this plugin is still in early stages and the underlying APIs (like filters, classes, etc.) may change.

== Installation ==

1. Upload the folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How do I customize the AMP output for my site? =

There are a number of filters available in the plugin for modifying the output. Advanced options like custom templates in the works. Note that these are not finalized and may change.

== Changelog ==

= 0.1 =
* Initial version
