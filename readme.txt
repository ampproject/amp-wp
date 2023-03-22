=== AMP ===
Contributors: google, xwp, rtcamp, automattic, westonruter, albertomedina, schlessera, delawski, swissspidy, pierlo, joshuawold
Tags: page experience, performance, amp, mobile, optimization, accelerated mobile pages
Requires at least: 4.9
Tested up to: 6.2
Stable tag: 2.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 7.0

An easier path to great Page Experience for everyone. Powered by AMP.

== Description ==

[Page Experience](https://developers.google.com/search/docs/guides/page-experience) (PX) is a set of ranking signals—including [Core Web Vitals](https://web.dev/vitals/#core-web-vitals) (CWV)—measuring the user experience of interacting with a web page. AMP is a powerful tool which applies many optimizations and best practices automatically on your site, making it easier for you to achieve good page experience for your visitors. The official AMP Plugin, supported by the AMP team, makes it easy to bring the power of AMP to your WordPress site, seamlessly integrating with the normal publishing flow and allowing the use of existing themes and plugins.

https://www.youtube.com/watch?v=s52JNMT59s8&list=PLXTOW_XMsIDRGRr5QDffrvND8Qh1RndFb

For more videos like this, check out the ongoing [AMP for WordPress video series](https://www.youtube.com/playlist?list=PLXTOW_XMsIDRGRr5QDffrvND8Qh1RndFb).

The plugin's key features include:

1. **Automate the process of generating AMP-valid markup as much as possible**, letting users follow the standard workflows they are used to in WordPress.
2. **Provide effective validation tools** to help users deal with AMP incompatibilities when they happen, including mechanisms for **identifying**, **contextualizing**, and **resolving issues caused by validation errors**.
3. **Provide development support** to make it easier for WordPress developers to build AMP-compatible ecosystem components and build websites and solutions with AMP-compatibility built-in.
4. **Support the serving of AMP pages** to make it easier for site owners to take advantage of mobile redirection, AMP-to-AMP linking, and generation of optimized AMP by default (via PHP port of AMP Optimizer).
5. **Provide a turnkey solution** for segments of WordPress creators to be able to go from zero to publishing AMP pages in no time, regardless of technical expertise or availability of resources.

The official AMP plugin for WordPress is a powerful tool that helps you build user-first WordPress sites, that is, sites that are fast, beautiful, secure, engaging, and accessible. A user-first site will deliver experiences that delight your users and therefore will increase user engagement and the success of your site. And, contrary to the popular belief of being only for mobile sites (it doesn't stand for Accelerated _Mobile_ Pages anymore!), AMP is a fully responsive web component framework, which means that you can provide AMP experiences for your users on both mobile and desktop devices.

= AMP Plugin Audience: Everyone =

This plugin can be used by both developers and non-developer users:

- If you are a developer or tech savvy user, you can take advantage of advanced developer tools provided by the AMP plugin to fix validation issues your site may have and reach full AMP compatibility.
- If you are not a developer or tech savvy user, or you just simply don't want to deal with validation issues and tackling development tasks, the AMP plugin allows you to assemble fully AMP-compatible sites with different configurations taking advantage of AMP-compatible components. The plugin helps you to deal with validation issues by removing invalid AMP markup in cases where it is possible, or altogether suppressing AMP-incompatible plugins on AMP pages.

The bottom line is that regardless of your technical expertise, the AMP plugin can be useful to you.

= Template Modes =

The official AMP plugin enables site owners to serve AMP to their users in different ways, which are referred to as template modes: Standard, Transitional, and Reader. The differences between them are in terms of the number of themes used (one or two), and the number of versions of the site (non-AMP, AMP). Each template mode brings its own value proposition and serves the needs of different scenarios in the large and diverse WordPress ecosystem. And in all cases, the AMP plugin provides as much support as possible in terms of automating the generation of AMP pages, as well as keeping the option chosen AMP valid. In a nutshell, the available template modes are the following:

**Standard Mode**: This template mode is the ideal, as there is only one theme for serving requests and a single version of your site: the AMP version. Besides enabling all of your site to be AMP-first, this has the added benefit of reducing development and maintenance costs. This mode is the best choice for sites where the theme and plugins used in the site are fully AMP-compatible. It&#39;s also a good option if some components are not AMP-compatible but the site owner has the resources or the know-how to fix them. See our [showcase](https://amp-wp.org/showcases/?template_mode=standard) of sites using Standard mode.

**Transitional Mode**: In this mode there is also a single theme used, but there can be two versions of each page: AMP and non-AMP. The active theme is used for serving the AMP and non-AMP versions of a given URL. This mode is a good choice if the site uses a theme that is not fully AMP compatible, but the functional differences between the AMP and non-AMP pages are acceptable (due to graceful degradation). In this case, users accessing the site from mobile devices can get the AMP version and get an optimized experience which also retains the look and feel of the non-AMP version. Check out our [showcase](https://amp-wp.org/showcases/?template_mode=transitional) of sites using Transitional mode.

**Reader Mode**: In this mode there are two different themes, one for AMP pages and another for non-AMP pages, and therefore there are also two versions of the site. This mode may be selected when the site is using an AMP-incompatible theme, but the level of incompatibilities is significant without graceful degradation. It's also a good choice if you are not technically savvy (or simply do not want to deal with the incompatibilities) and therefore want simplified and robust workflows that allow you to take advantage of AMP with minimal effort.

Different modes would be recommended in different scenarios, depending on the specifics of your site and your role. As you configure the plugin, it will suggest the mode that might be best for you based on its assessment of the theme and plugins used on your site. And, independently of the mode used, you have the option of serving all or only a portion of your site as AMP. This gives you all the flexibility you need to get started enabling AMP on your site progressively.

= AMP Ecosystem =

It is possible today to assemble great looking user-first sites powered by the AMP plugin by picking and choosing themes and plugins from a growing AMP-compatible ecosystem. In this context, the AMP plugin acts as an orchestrator of the overall AMP content creation and publishing process; it serves as a validator and enforcer making it easier to not only get to AMP experiences, but to maintain them with confidence.

Many popular theme and plugin developers have taken efforts to support the official AMP plugin. If you are using a theme like Astra or Newspack, or if you are using plugins like Yoast or WP Forms — they will work out of the box! You can see the [growing list](https://amp-wp.org/ecosystem/) of tested themes and plugins.

= AMP Development =

Although there is a growing ecosystem of AMP-compatible WordPress components, there is still a ways to go before majority AMP compatibility in the ecosystem. If you are a developer, or you have the resources to pursue development projects, you may want in some cases to develop custom plugin or theme to serve your specific needs. The official AMP plugin can be of great help to you by providing powerful and effective developer tools that shed light into the AMP development process as it is done in WordPress. This includes mechanisms for detailing the root causes of validation issues, the contextual space to understand them properly, and methods to deal with them during the process of achieving full AMP compatibility. Read more about [Developer Tools](https://amp-wp.org/documentation/getting-started/developer-tools/).

= Getting Started =

To learn more about the plugin and start leveraging its capabilities to power your AMP publishing workflow, check [the official AMP plugin product site](https://amp-wp.org/).

If you are a developer, we encourage you to [follow along](https://github.com/ampproject/amp-wp) or [contribute](https://github.com/ampproject/amp-wp/wiki/Contributing) to the development of this plugin on GitHub.

We have put up a comprehensive [FAQ page](https://amp-wp.org/documentation/frequently-asked-questions/) and extensive documentation to help you start as smoothly as possible.

But if you need some help, we are right here to support you in the plugin's [support forum](https://wordpress.org/support/plugin/amp/), as well as through [GitHub issues](https://github.com/ampproject/amp-wp/issues) (for technical bugs and feature requests). And our thriving [AMP Expert ecosystem](https://amp-wp.org/ecosystem/amp-experts/) has indie freelancers to enterprise grade agencies in case you need commercial support!

== Installation ==

1. Upload the folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Navigate to AMP > Settings in the WordPress admin to configure the plugin; use the onboarding wizard there for guided setup.

== Frequently Asked Questions ==

Please see the [FAQs on amp-wp.org](https://amp-wp.org/documentation/frequently-asked-questions/). Don't see an answer to your question? Please [search the support forum](https://wordpress.org/support/plugin/amp/) to see if it has already been discussed. Otherwise, please [open a new support topic](https://wordpress.org/support/plugin/amp/#new-post).

== Screenshots ==

1. New onboarding wizard to help you get started.
2. Built for developers and non-technical content creators alike.
3. Theme selection to enhance the Reader mode experience.
4. Preview how your site looks across desktop and mobile before finalising changes.
5. Customize the design of AMP pages in the Customizer.
6. Reopen the onboarding wizard, change individual options, or manage advanced settings.

== Changelog ==

For the plugin’s changelog, please see [the Releases page on GitHub](https://github.com/ampproject/amp-wp/releases).

== Upgrade Notice ==

If you currently use older versions of the plugin in Reader mode, it is strongly encouraged to pick a Reader theme instead of using the legacy Reader templates. You may also want to switch to Standard mode or Transitional mode if you have AMP-compatible theme and plugins.
