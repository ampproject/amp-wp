Languages
=========

The AMP plugin uses WP-CLI and the `@wordpress/i18n` package to enable internationalization in both PHP and JavaScript.

The WordPress.org translation platform automatically extracts strings from plugins and makes them available for translators.

However, since it doesn't yet support JavaScript string extraction, strings inside JavaScript files are copied to an automatically generated PHP file when building the plugin.

To create this file locally, run the following command:

```
npm run build
```

After the build completes, you'll find an `amp-translations.php` file in this directory.
