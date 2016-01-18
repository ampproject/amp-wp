# AMP for WordPress

## Overview

This plugin adds support for the Accelerated Mobile Pages (AMP) Project, which is an an open source initiative that aims to provide mobile optimized content that can load instantly everywhere.

With the plugin active, all content on your site will have dynamically generated AMP-compatible versions, accessible by appending `/amp/` to the end your permalinks. (If you do not have pretty permalinks enabled, you can do the same thing by appending `?amp=1`.)

Developers: please note that this plugin is still in early stages and the underlying APIs (like filters, classes, etc.) may change.

## Customization

The plugin ships with a default template that looks nice and clean. For customization, there are various options.

### Theme Mods

The default template will attempt to draw from various theme mods, such as site icon and background and header color/image, if supported by the active theme.

### Custom Template

For more control, you can override the default template using the `amp_template_file` filter and pass it the path to a custom template:

There are some requirements for a custom template:

* The path must pass the default criteria set out by `[validate_file](https://developer.wordpress.org/reference/functions/validate_file/)`.
* You must trigger the `amp_head` action in the `<head>` section:

```
do_action( 'amp_head', $amp_post );
```

* You must trigger the `amp_footer` action right before the `</body>` tag:

```
do_action( 'amp_footer', $amp_post );
```

* You must include [all required mark-up](https://www.ampproject.org/docs/get_started/create/basic_markup.html) that isn't already output via the `amp_head` action.
