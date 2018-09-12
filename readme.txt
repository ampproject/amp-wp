=== AMP for WordPress ===
Contributors: batmoo, joen, automattic, potatomaster, albertomedina, google, xwp, westonruter
Tags: amp, mobile
Requires at least: 4.7
Tested up to: 4.9
Stable tag: 0.7.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires PHP: 5.3.6

Enable Accelerated Mobile Pages (AMP) on your WordPress site.

== Description ==

Bring the speed and features of the open source [AMP project](https://www.ampproject.org/) to your site, the WordPress way.

With the plugin active, all posts on your site will have AMP-compatible versions, accessible by appending `/amp/` to the end your post URLs. For example, if your post URL is `http://example.com/2016/01/01/amp-on/`, you can access the AMP version at `http://example.com/2016/01/01/amp-on/amp/`. If you do not have [pretty permalinks](https://codex.wordpress.org/Using_Permalinks#mod_rewrite:_.22Pretty_Permalinks.22) enabled, you can do the same thing by appending `?amp=1`, i.e. `http://example.com/?p=123&amp=1`

Also, your pages and custom post types can have AMP versions. Simply check their boxes on the 'AMP Settings' page in `/wp-admin`.

Your entire site can render as "Native AMP" if your theme calls `add_theme_support( 'amp' )`. There will only be one version of each URL: the AMP version. There won't be separate URLs with `/amp` or `?amp` appended. See this [wiki page](https://github.com/Automattic/amp-wp/wiki/Adding-Theme-Support#native-amp) for details and restrictions.

Your theme can also use [Paired Mode](https://github.com/Automattic/amp-wp/wiki/Adding-Theme-Support#paired-mode), with your own custom templates for the AMP URLs.

"Native AMP" and "Paired Mode" add full support for commenting and widgets.

If your theme doesn't support `'amp'`, this will use basic legacy post templates for AMP consumers like Google Search and Twitter. And when visiting the site, the AMP content won't normally appear without appending strings to the URL like `/amp` or `?amp`.

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

= 1.0 (unreleased) =

To learn how to use the new features in this release, please see the wiki pages for [Adding Theme Support](https://github.com/Automattic/amp-wp/wiki/Adding-Theme-Support) and [Implementing Interactivity](https://github.com/Automattic/amp-wp/wiki/Implementing-Interactivity).

- Add runtime CSS minification, `!important` replacement, and tree shaking. See [#1048](https://github.com/Automattic/amp-wp/pull/1048), [#1111](https://github.com/Automattic/amp-wp/pull/1111), [#1142](https://github.com/Automattic/amp-wp/pull/1142), [#1320](https://github.com/Automattic/amp-wp/pull/1320), [#1073](https://github.com/Automattic/amp-wp/issues/1073). Props westonruter, hellofromtonya, amedina, pbakaus, igrigorik, camelburrito.
- Add ability to acknowledge and suppress/ignore specific validation errors. See [#1003](https://github.com/Automattic/amp-wp/issues/1003). Props westonruter.
- Extend admin screen options to add `amp` theme support without any coding required. Toggle between classic, paired, and native. Includes options for whether sanitization should be done by default and whether tree shaking should always be allowed. See [#1199](https://github.com/Automattic/amp-wp/pull/1199), [#1291](https://github.com/Automattic/amp-wp/pull/1291), [#1264](https://github.com/Automattic/amp-wp/issues/1264). Props westonruter, AdelDima.
- Add an admin pointer for updated AMP settings screen for version 1.0. See [#1271](https://github.com/Automattic/amp-wp/pull/1271), [#1254](https://github.com/Automattic/amp-wp/issues/1254). Props kienstra.
- Add support for three core themes (Twenty Fifteen, Twenty Sixteen, Twenty Seventeen) so that they can be used out of the box with AMP theme support added without needing to create a child theme. See [#1074](https://github.com/Automattic/amp-wp/pull/1074). Props westonruter, DavidCramer, kienstra, .
- Add support for allowing a site subset to be native AMP. See [#1235](https://github.com/Automattic/amp-wp/pull/1235). Props westonruter.
- Add AMP menu item to admin bar on frontend with indication of AMP validation status; accessing an AMP URL that has unaccepted validation errors will redirect to the non-AMP page and cause the AMP admin bar item to indicate the failure, along with a link to access the validation results. See [#1199](https://github.com/Automattic/amp-wp/pull/1199). Props westonruter.
- Add dynamic handling of validation errors. See [#1093](https://github.com/Automattic/amp-wp/pull/1093), [#1063](https://github.com/Automattic/amp-wp/pull/1063), [#1087](https://github.com/Automattic/amp-wp/issues/1087). Props westonruter.
- Add AMP validation of blocks. See [#1019](https://github.com/Automattic/amp-wp/pull/1019). Props westonruter.
- Add AMP-specific functionality to core blocks. See [#1026](https://github.com/Automattic/amp-wp/pull/1026), [#1008](https://github.com/Automattic/amp-wp/issues/1008). Props miina.
- Add AMP media blocks (when in native AMP mode). See [#1155](https://github.com/Automattic/amp-wp/pull/1155). Props miina.
- Add embed handler for Gfycat. See [#1136](https://github.com/Automattic/amp-wp/pull/1136). Props miina.
- Add amp-mathml block. See [#1165](https://github.com/Automattic/amp-wp/pull/1165). Props miina.
- Add Gutenberg amp-timeago block. See [#1168](https://github.com/Automattic/amp-wp/pull/1168). Props miina.
- Add `amp-fit-text` support to text blocks. See [#1151](https://github.com/Automattic/amp-wp/pull/1151). Props miina.
- Fix handling of font stylesheets with non-HTTPS scheme or scheme-less URLs. See [#1077](https://github.com/Automattic/amp-wp/pull/1077). Props westonruter.
- Fix issues in displaying native blocks. See [#1022](https://github.com/Automattic/amp-wp/pull/1022). Props miina.
- Gutenberg: Add AMP Carousel for Gallery and AMP Lightbox features for Gallery and Image. See [#1121](https://github.com/Automattic/amp-wp/pull/1121), [#1065](https://github.com/Automattic/amp-wp/issues/1065), [#1187](https://github.com/Automattic/amp-wp/pull/1187). Props miina, westonruter.
- Add "Enable AMP" toggle in Gutenberg editor. See [#1275](https://github.com/Automattic/amp-wp/pull/1275), [#1230](https://github.com/Automattic/amp-wp/issues/1230). Props kienstra.
- Cache post processor response. See [#1156](https://github.com/Automattic/amp-wp/pull/1156), [#959](https://github.com/Automattic/amp-wp/issues/959). Props ThierryA.
- Add preload links & resource hints, and optimize order of elements in head. See [#1295](https://github.com/Automattic/amp-wp/pull/1295). Props westonruter.
- Automatically redirect to `?amp` from `/amp/` URLs when `amp` theme support is present. See [#1203](https://github.com/Automattic/amp-wp/pull/1203), [#1194](https://github.com/Automattic/amp-wp/pull/1194). Props westonruter.
- Incorporate Server Timing API. See [#990](https://github.com/Automattic/amp-wp/issues/990). Props westonruter.
- Add information about stylesheets included and excluded in `style[amp-custom]`. See [#1135](https://github.com/Automattic/amp-wp/pull/1135). Props westonruter.
- Fetch (local) stylesheets with `@import`, instead of removing them. See [#1181](https://github.com/Automattic/amp-wp/pull/1181). Props miina.
- Fetch external stylesheets (which aren't from whitelisted font CDNs) to include in amp-custom style. See [#1174](https://github.com/Automattic/amp-wp/pull/1174). Props miina.
- Transform CSS selectors according to sanitizer HTML element to AMP component conversions. See [#1175](https://github.com/Automattic/amp-wp/pull/1175). Props miina, westonruter.
- Ensure layout attributes are only allowed on supporting elements. See [#1075](https://github.com/Automattic/amp-wp/pull/1075). Props westonruter.
- Correct the width attribute in `col` tags to the equivalent CSS rule. See [#1064](https://github.com/Automattic/amp-wp/pull/1064). Props amedina.
- Ensure that video `source` elements use HTTPS. See [#1274](https://github.com/Automattic/amp-wp/pull/1274), [#976](https://github.com/Automattic/amp-wp/issues/976). Props hellofromtonya.
- Preserve whitespace when serializing the DOM as HTML. See [#1309](https://github.com/Automattic/amp-wp/pull/1309), [#1304](https://github.com/Automattic/amp-wp/issues/1304). Props westonruter.
- Fix reporting the removal of unrecognized elements. See [#1287](https://github.com/Automattic/amp-wp/pull/1287), [#1100](https://github.com/Automattic/amp-wp/issues/1100). Props hellofromtonya.
- Remove space from `data: url()` in stylesheets. See [#1164](https://github.com/Automattic/amp-wp/pull/1164/), [#1089](https://github.com/Automattic/amp-wp/issues/1089). Props amedina, JonHendershot, westonruter, mehigh, davisshaver, Mte90.
- Fix inconsistency between singular and plural. See [#1114](https://github.com/Automattic/amp-wp/pull/1114). Props garrett-eclipse.
- Disable AMP admin menu option when the AMP Customizer is not enabled or theme support is enabled. See [#1080](https://github.com/Automattic/amp-wp/pull/1080). Props oscarssanchez.
- Allow spaces around commas in value property lists. See [#1112](https://github.com/Automattic/amp-wp/pull/1112). Props westonruter.
- Restore admin bar on AMP pages and improve AMP menu items. See [#1219](https://github.com/Automattic/amp-wp/pull/1219). Props westonruter.
- Display admin notice if there's no persistent object caching. See [#1050](https://github.com/Automattic/amp-wp/pull/1050). Props oscarssanchez.
- Update PHP-CSS-Parser to use new calc() support. See [#1116](https://github.com/Automattic/amp-wp/pull/1116), [#1284](https://github.com/Automattic/amp-wp/pull/1284). Props westonruter.
- Fix parsing CSS selectors which contain commas. See [#1286](https://github.com/Automattic/amp-wp/pull/1286). Props westonruter.
- Add sanitizer to support `amp-o2-player`. See [#1202](https://github.com/Automattic/amp-wp/pull/1202). Props juanchaur1.
- Add `AMP_Embed_Sanitizer`. See [#1128](https://github.com/Automattic/amp-wp/pull/1128). Props juanchaur1.
- Add `AMP_Script_Sanitizer` to replace `noscript` elements with their contents. See [#1226](https://github.com/Automattic/amp-wp/pull/1226). Props westonruter.
- Fix header image filtering and YouTube header video detection. See [#1208](https://github.com/Automattic/amp-wp/pull/1208). Props westonruter.
- Improve support for Hulu & Imgur embeds. See [#1218](https://github.com/Automattic/amp-wp/pull/1218). Props miina.
- Update spec generated from amphtml to file revision 675 and AMP v1531357871900. See [#1312](https://github.com/Automattic/amp-wp/pull/1312). Props westonruter.
- Opt-in to CORS mode for external font stylesheet links. See [#1289](https://github.com/Automattic/amp-wp/pull/1289). Props westonruter.
- PHPCS fixes, including PHP DocBlocks and strict comparisons. See [#1002](https://github.com/Automattic/amp-wp/pull/1002). Props paulschreiber.
- Add script to create built tag. See [#1209](https://github.com/Automattic/amp-wp/pull/1209). Props westonruter.
- Fix handling of amp-bind attributes to ensure that `“>”` can appear inside attribute values. See [#1119](https://github.com/Automattic/amp-wp/pull/1119). Props westonruter.
- Tree-shake CSS selectors for HTML elements that target non-active languages. See [#1221](https://github.com/Automattic/amp-wp/pull/1221). Props westonruter.
- Redirect to post list table in case of admin bar validate request failure. See [#1229](https://github.com/Automattic/amp-wp/pull/1229). Props westonruter.
- Prevent erroneously tree-shaking keyframe selectors like `from`, `to`, and percentages. See [#1211](https://github.com/Automattic/amp-wp/pull/1211). Props westonruter.
- Add caching of redirect to non-AMP URL when validation errors present. See [#1207](https://github.com/Automattic/amp-wp/pull/1207). Props westonruter.
- Move any content output during shutdown to be injected before closing body tag. See [#1102](https://github.com/Automattic/amp-wp/pull/1102). Props westonruter.
- Fix obtaining source for widgets. See [#1212](https://github.com/Automattic/amp-wp/pull/1212). Props westonruter.
- Construct schema.org meta script by appending text node. See [#1220](https://github.com/Automattic/amp-wp/pull/1220). Props westonruter.
- Eliminate `amp-wp-enforced-sizes` style from theme support stylesheet. See [#1153](https://github.com/Automattic/amp-wp/pull/1153). Props westonruter.
- Add support for extracting (pixel) dimensions from SVG images. See [#1150](https://github.com/Automattic/amp-wp/pull/1150). Props westonruter.
- Ensure redirect is only done if there are unsanitized errors. See [#1241](https://github.com/Automattic/amp-wp/pull/1241). Props westonruter.
- Deprecate `AMP_WP_Utils`, in favor of `wp_parse_url()`. See [#995](https://github.com/Automattic/amp-wp/pull/995). Props paulschreiber.
- Add WP-CLI script to test support for blocks. See [#845](https://github.com/Automattic/amp-wp/issues/845). Props kienstra.
- Ensure translatable strings in blocks can actually be translated. See [#1173](https://github.com/Automattic/amp-wp/pull/1173). Props miina, swissspidy, westonruter.
- Prevent `is_amp_endpoint()` from triggering notice when called on login, signup, or activate screens. See [#1250](https://github.com/Automattic/amp-wp/pull/1250). Props felixarntz.
- Support extracting dimensions for single URLs. See [#793](https://github.com/Automattic/amp-wp/pull/793). Props mjangda, mdbitz.
- Improve validation and presentation of analytics form. See [#1299](https://github.com/Automattic/amp-wp/pull/1299), [#1133](https://github.com/Automattic/amp-wp/issues/1133), [#1296](https://github.com/Automattic/amp-wp/pull/1296). Props westonruter, AdelDima.
- Prevent validation of auto-drafts, including when merely accessing New Post screen. See [#1301](https://github.com/Automattic/amp-wp/pull/1301). Props westonruter.
- Fix inability to move link element due to assigned parent. See [#1322](https://github.com/Automattic/amp-wp/issues/1322). Props westonruter.
- Fix stretched images in Twenty Seventeen them and Gutenberg. See [#1321](https://github.com/Automattic/amp-wp/issues/1321), [#1281](https://github.com/Automattic/amp-wp/issues/1281), [#1237](https://github.com/Automattic/amp-wp/issues/1237). Props hellofromtonya.
- Fix image dimension extractor so it does not disregard duplicate images. See [#1314](https://github.com/Automattic/amp-wp/issues/1314). Props lukas9393.
- Remove redundant version from composer.json and add PHP version requirement. See [#1333](https://github.com/Automattic/amp-wp/issues/1333), [#1328](https://github.com/Automattic/amp-wp/issues/1328), [#1334](https://github.com/Automattic/amp-wp/issues/1334), [#1332](https://github.com/Automattic/amp-wp/issues/1332). Props swissspidy.
- Store validation errors in order of occurrence in document. See [#1335](https://github.com/Automattic/amp-wp/issues/1335). Props westonruter.
- Add .editorconfig file. See [#1336](https://github.com/Automattic/amp-wp/issues/1336), [#51](https://github.com/Automattic/amp-wp/issues/51). Props swissspidy.
- Update i18n to make use of updated WP-CLI command. See [#1329](https://github.com/Automattic/amp-wp/issues/1329), [#1327](https://github.com/Automattic/amp-wp/issues/1327), [#1341](https://github.com/Automattic/amp-wp/issues/1341), [#1345](https://github.com/Automattic/amp-wp/issues/1345), [#1393](https://github.com/Automattic/amp-wp/issues/1393). Props swissspidy, felixarntz, westonruter.
- Use all eligible post types when `all_templates_supported` is selected. See [#1338](https://github.com/Automattic/amp-wp/issues/1338), [#1302](https://github.com/Automattic/amp-wp/issues/1302), [#1344](https://github.com/Automattic/amp-wp/issues/1344). Props hellofromtonya, westonruter.
- Respect default AMP enabled status when creating a new post in Gutenberg. See [#1339](https://github.com/Automattic/amp-wp/issues/1339). Props hellofromtonya.
- Normalize 'ver' query param in script/style validation errors to prevent recurrence after accepted. See [#1346](https://github.com/Automattic/amp-wp/issues/1346). Props westonruter.
- Add missing tabindex attribute to lightbox images. See [#1350](https://github.com/Automattic/amp-wp/issues/1350). Props amedina.
- Detect ineffectual post-processor response cache due to high MISS rates and auto-disable. See [#1325](https://github.com/Automattic/amp-wp/issues/1325), [#1239](https://github.com/Automattic/amp-wp/issues/1239). Props hellofromtonya, westonruter.
- Update the validator spec version to 720 and AMP v1534879991178; add support for reference points. See [#1315](https://github.com/Automattic/amp-wp/issues/1315), [#1386](https://github.com/Automattic/amp-wp/issues/1386), [#1330](https://github.com/Automattic/amp-wp/issues/1330). Props westonruter.
- Fix form sanitizer's handling of relative actions by making them absolute. See [#1352](https://github.com/Automattic/amp-wp/issues/1352), [#1349](https://github.com/Automattic/amp-wp/issues/1349). Props ricardobrg.
- Skip Server-Timing header if not WP_DEBUG and user cannot manage_options. See [#1354](https://github.com/Automattic/amp-wp/issues/1354). Props westonruter.
- Fetch CSS over HTTP when URL lacks extension; convert font CDN stylesheets @imports to convert to links instead of fetching. See [#1357](https://github.com/Automattic/amp-wp/issues/1357), [#1317](https://github.com/Automattic/amp-wp/issues/1317). Props westonruter.
- Add WP-CLI command for testing the AMP compatibility of an entire site. See [#1183](https://github.com/Automattic/amp-wp/issues/1183), [#1007](https://github.com/Automattic/amp-wp/issues/1007). Props kienstra, westonruter.
- Display when validation results are stale due to active theme/plugin changes. See [#1375](https://github.com/Automattic/amp-wp/issues/1375). Props westonruter.
- Fix displaying of expected notices when theme support enabled by theme. See [#1374](https://github.com/Automattic/amp-wp/issues/1374), [#1358](https://github.com/Automattic/amp-wp/issues/1358). Props westonruter.
- Fix handling responses to form submissions from an AMP Cache. See [#1382](https://github.com/Automattic/amp-wp/issues/1382), [#1356](https://github.com/Automattic/amp-wp/issues/1356).
- Replace Gutenberg's deprecated isCleanNewPost selector. See [#1387](https://github.com/Automattic/amp-wp/issues/1387). Props miina.
- Updates php-css-parser to include fix for parsing calc() with negative values. See [#1392](https://github.com/Automattic/amp-wp/issues/1392). Props westonruter.
- Add embed support for Twitter timelines via new amp-twitter attributes. See [#1396](https://github.com/Automattic/amp-wp/issues/1396). Props felixarntz.
- Add error type filters on validation error and invalid URL screens. See [#1373](https://github.com/Automattic/amp-wp/issues/1373). Props kienstra.
- Default to auto sanitization and tree shaking being enabled. See [#1402](https://github.com/Automattic/amp-wp/issues/1402). Props westonruter.

For a full list of the closed issues and merged pull requests in this release, see the [1.0 milestone](https://github.com/Automattic/amp-wp/milestone/7?closed=1).

Contributors in this release, including design, development, testing, and project management: Adel Tahri (AdelDima), Alberto Medina (amedina), Claudio Sossi, Daniel Walmsley (gravityrail), David Cramer (DavidCramer), Felix Arntz (felixarntz), Garrett Hyder (garrett-eclipse), Joshua Wold (jwold), Juan Chaur (juanchaur1), Kevin Coleman (kevincoleman), Leo Postovoit (postphotos), Lukas Hettwer (lukas9393), Mackenzie Hartung (MackenzieHartung), Matthew Denton (mdbitz), Miina Sikk (miina), Mohammad Jangda (mjangda), Oscar Sánchez (oscarssanchez), Paul Schreiber (paulschreiber), Ricardo Gonçalves (ricardobrg), Ryan Kienstra (kienstra), Thierry Muller (ThierryA), Tonya Mork (hellofromtonya), Weston Ruter (westonruter).

= 0.7.2 (2018-06-27) =

- Prevent plugins from outputting custom scripts in classic templates via `wp_print_scripts` action. See [#1225](https://github.com/Automattic/amp-wp/issues/1225), [#1227](https://github.com/Automattic/amp-wp/pull/1227). Props westonruter.
- Introduce `amp_render_scripts()` to print AMP component scripts and nothing else. See [#1227](https://github.com/Automattic/amp-wp/pull/1227). Props westonruter.
- Display Schema.org image data for 'attachment' post type. See [#1157](https://github.com/Automattic/amp-wp/issues/1157), [#1176](https://github.com/Automattic/amp-wp/pull/1176). Props kienstra.
- Output `alt` attribute in legacy templating gravatar image. See [#1179](https://github.com/Automattic/amp-wp/pull/1179). Props kienstra.

See [0.7.2 milestone](https://github.com/Automattic/amp-wp/milestone/9?closed=1).

= 0.7.1 (2018-05-23) =

- Limit showing AMP validation warnings to when `amp` theme support is present. See [#1132](https://github.com/Automattic/amp-wp/pull/1132). Props westonruter.
- Supply the extracted dimensions to images determined to need them; fixes regression from 0.6 this is key for Gutenberg compat. See [#1117](https://github.com/Automattic/amp-wp/pull/1117). Props westonruter.
- Ensure before/after is amended to filtered comment_reply_link. See [#1118](https://github.com/Automattic/amp-wp/pull/1118). Props westonruter.
- Force VideoPress to use html5 player for AMP. See [#1125](https://github.com/Automattic/amp-wp/pull/1125). Props yurynix.
- Soft-deprecate `AMP_Base_Sanitizer::get_body_node()` instead of hard-deprecating it (with triggered notice). See [#1141](https://github.com/Automattic/amp-wp/pull/1141). Props westonruter.
- Pass '/' as an argument to home_url(), preventing possible 404. See [#1158](https://github.com/Automattic/amp-wp/issues/1158), [#1161](https://github.com/Automattic/amp-wp/pull/1161). Props kienstra.
- Deprecate Jetpack helper and some parts of WPCOM helper for Jetpack 6.2. See [#1149](https://github.com/Automattic/amp-wp/pull/1149). Props gravityrail.

See [0.7.1 milestone](https://github.com/Automattic/amp-wp/milestone/8?closed=1).

= 0.7.0 (2018-05-03) =

- Render an entire site as "Native AMP" if the theme calls `add_theme_support( 'amp' )`. See [#857](https://github.com/Automattic/amp-wp/pull/857), [#852](https://github.com/Automattic/amp-wp/pull/852), [#865](https://github.com/Automattic/amp-wp/pull/865), [#888](https://github.com/Automattic/amp-wp/pull/888). Props westonruter, kaitnyl, ThierryA.
- Use the AMP spec to automatically discover the required AMP component scripts to include on the page while post-processing. See [#882](https://github.com/Automattic/amp-wp/pull/882), [#885](https://github.com/Automattic/amp-wp/pull/885). Props westonruter.
- Automatically concatenate stylesheets from `style` tags with loaded stylesheets from `link` tags combined in one `style[amp-custom]`. See [#887](https://github.com/Automattic/amp-wp/pull/887), [#890](https://github.com/Automattic/amp-wp/pull/890), [#935](https://github.com/Automattic/amp-wp/pull/935). Props westonruter.
- Update serialization to use HTML instead of XML; update minimum version of PHP fro, 5.2 to 5.3. See [#891](https://github.com/Automattic/amp-wp/pull/891).
- Add support for widgets. See [#870](https://github.com/Automattic/amp-wp/pull/870). Props kienstra.
- Add support for forms. See [#907](https://github.com/Automattic/amp-wp/pull/907), [#923](https://github.com/Automattic/amp-wp/pull/923). Props DavidCramer.
- Use "Paired Mode" if the theme calls `add_theme_support( 'amp' )` and passes a `'template_dir'` value for the AMP templates. See [#856](https://github.com/Automattic/amp-wp/pull/856), [#877](https://github.com/Automattic/amp-wp/pull/877). Props westonruter, kaitnyl.
- Add AMP implementations of audio/video playlists. See [#954](https://github.com/Automattic/amp-wp/pull/954). Props kienstra.
- Allow full Customization when the theme supports `'amp'`. See [#952](https://github.com/Automattic/amp-wp/pull/952). Props westonruter.
- Add support for all default WordPress widgets. See [#921](https://github.com/Automattic/amp-wp/pull/921), [#917](https://github.com/Automattic/amp-wp/pull/917). Props kienstra, westonruter.
- Add support for more default embeds: Issuu, Post, Meetup, Reddit, Screencast, Tumblr, and WordPress Plugin Directory. See [#889](https://github.com/Automattic/amp-wp/pull/889). Props kaitnyl.
- Allow native WordPress commenting, in fully valid AMP. See [#1024](https://github.com/Automattic/amp-wp/pull/1024), [#1029](https://github.com/Automattic/amp-wp/pull/1029), [#871](https://github.com/Automattic/amp-wp/pull/871), [#909](https://github.com/Automattic/amp-wp/pull/909). Props DavidCramer, westonruter.
- Add a UI for displaying validation errors, including invalid tags and attributes, with tracing for the source for each error according to which theme/plugin's shortcode, widget, or other hook is responsible. Includes debug mode to suspend sanitizer. See [#971](https://github.com/Automattic/amp-wp/pull/971), [#1012](https://github.com/Automattic/amp-wp/pull/1012), [#1016](https://github.com/Automattic/amp-wp/pull/1016). Props westonruter, kienstra.
- On activating a plugin, validate a front-end page and display a notice if there were errors. See [#971](https://github.com/Automattic/amp-wp/pull/971). Props westonruter, kienstra.
- Creation of AMP-related notifications, on entering invalid content in the 'classic' editor. See [#912](https://github.com/Automattic/amp-wp/pull/912/). Props kienstra, westonruter, ThierryA.
- Optionally use `<amp-live-list>` to display comments, avoiding full-page refreshes on adding comments. And enable making requests for an `<amp-live-list>`, like for displaying posts. See [#1029](https://github.com/Automattic/amp-wp/pull/1029), [#915](https://github.com/Automattic/amp-wp/pull/915). Props DavidCramer, westonruter.
- Support `<amp-bind>`, enabling more dynamic elements. See [#895](https://github.com/Automattic/amp-wp/pull/895). Props westonruter.
- Add output buffering, ensuring the entire page is valid AMP. See [#929](https://github.com/Automattic/amp-wp/pull/929), [#857](https://github.com/Automattic/amp-wp/pull/857), [#931](https://github.com/Automattic/amp-wp/pull/931). Props westonruter, ThierryA.
- Add validation of host names in URLs. See [#983](https://github.com/Automattic/amp-wp/pull/983). Props rubengonzalezmrf.
- Add WP-CLI scripts to test AMP support of comments and widgets. See [#924](https://github.com/Automattic/amp-wp/pull/924), [#859](https://github.com/Automattic/amp-wp/pull/859). Props DavidCramer, kienstra.
- Improve test coverage, including for `AMP_Theme_Support`. See [#1034](https://github.com/Automattic/amp-wp/pull/1034). Props DavidCramer, kienstra.
- Update the generated sanitizer file to the AMP spec, and simplify the file that generates it. See [#929](https://github.com/Automattic/amp-wp/pull/929), [#926](https://github.com/Automattic/amp-wp/pull/926). Props westonruter.
- Several sanitizer updates, including for styles, and preventing valid tags from being removed. See [#935](https://github.com/Automattic/amp-wp/pull/935), [#944](https://github.com/Automattic/amp-wp/pull/944), [#952](https://github.com/Automattic/amp-wp/pull/952). Props westonruter, davisshaver.
- Improve sanitization of `<amp-img>`, `<amp-video>`, and `<amp-iframe>`. See [#937](https://github.com/Automattic/amp-wp/pull/937), [#1054](https://github.com/Automattic/amp-wp/pull/1054). Props kienstra, amedina.
- Fix an issue where the JSON inside `<script type="application/json">` was wrapped with CDATA. See [#891](https://github.com/Automattic/amp-wp/pull/891). Props westonruter.
- Allow use of AMP components outside of AMP documents, including in [PWA](https://developers.google.com/web/progressive-web-apps/). See [#1013](https://github.com/Automattic/amp-wp/pull/1013). Props westonruter.
- Access the AMP query var with `amp_get_slug()`, instead of `AMP_QUERY_VAR`. See [#986](https://github.com/Automattic/amp-wp/pull/986). Props westonruter, mjangda.
- Update build scripts, including PHP versions in `.travis.yml`. See [#1058](https://github.com/Automattic/amp-wp/pull/1058/), [#949](https://github.com/Automattic/amp-wp/pull/949). Props westonruter.
- Prevent New Relic script from being injected in AMP responses. See [#932](https://github.com/Automattic/amp-wp/pull/932). Props westonruter.
- Fix handling of 0 and empty height/width attributes. See [#979](https://github.com/Automattic/amp-wp/pull/979). Props davisshaver.

For a full list of the closed issues and merged pull requests in this release, see the [0.7 milestone](https://github.com/Automattic/amp-wp/milestone/6?closed=1).

Contributors in this release, including design, development, testing, and project management: Adam Silverstein (adamsilverstein), Alberto Medina (amedina), Christian Chung (christianc1), Claudio Sossi, David Cramer (DavidCramer), Davis Shaver (davisshaver), Douglas Paul (douglyuckling), Jason Johnston (jhnstn), Joshua Wold (jwold), Kaitlyn (kaitnyl), Leo Postovoit (postphotos), Mackenzie Hartung (MackenzieHartung), Maxim Siebert (MaximSiebert), Mike Crantea (mehigh), Mohammad Jangda (mjangda), Oscar Sanchez (oscarssanchez), Philip John (philipjohn), Piotr Delawski (delawski), Renato Alves (renatonascalves), Rubén (rubengonzalezmrf), Ryan Kienstra (kienstra), Thierry Muller (ThierryA), vortfu, Weston Ruter (westonruter), Ziga Sancin (zigasancin).

= 0.6.2 (2018-02-28) =

* Improve logic and use of escaping; limit flushing rewrite rules to only when supported_post_types change. See [#953](https://github.com/Automattic/amp-wp/pull/953). Props philipjohn, westonruter.
* Fix AMP preview icon in Firefox. See [#920](https://github.com/Automattic/amp-wp/pull/920). Props zigasancin.

= 0.6.1 (2018-02-09) =

Version bump to re-release plugin in order to deal with missing file in 0.6.0 release package that caused fatal error.

= 0.6.0 (2018-01-23) =

- Add support for the "page" post type. A new `page.php` is introduced with template parts factored out (`html-start.php`, `header.php`, `footer.php`, `html-end.php`) and re-used from `single.php`. Note that AMP URLs will end in `?amp` instead of `/amp/`. See [#825](https://github.com/Automattic/amp-wp/pull/825). Props technosailor, ThierryA, westonruter.
- Add AMP post preview button alongside non-AMP preview button. See [#813](https://github.com/Automattic/amp-wp/pull/813). Props ThierryA, westonruter.
- Add ability to disable AMP on a per-post basis via toggle in publish metabox. See [#813](https://github.com/Automattic/amp-wp/pull/813). Props ThierryA, westonruter.
- Add AMP settings admin screen for managing which post types have AMP support, eliminating the requirement to add `add_post_type_support()` calls in theme or plugin. See [#811](https://github.com/Automattic/amp-wp/pull/811). Props ThierryA, westonruter.
- Add generator meta tag for AMP. See [#810](https://github.com/Automattic/amp-wp/pull/810). Props vaporwavre.
- Add code quality checking via phpcs, eslint, jscs, and jshint. See [#795](https://github.com/Automattic/amp-wp/pull/795). Props westonruter.
- Add autoloader to reduce complexity. See [#828](https://github.com/Automattic/amp-wp/pull/828). Props mikeschinkel, westonruter, ThierryA.
- Fix Polldaddy amd SoundCloud embeds. Add vanilla WordPress "embed" test page. A new `bin/create-embed-test-post.php` WP-CLI script is introduced. See [#829](https://github.com/Automattic/amp-wp/pull/829). Props kienstra, westonruter, ThierryA.
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
