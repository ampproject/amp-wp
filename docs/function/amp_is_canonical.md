## Function `amp_is_canonical`

```php
function amp_is_canonical();
```

Whether this is in &#039;canonical mode&#039;.

Themes can register support for this with `add_theme_support( AMP_Theme_Support::SLUG )`:
      add_theme_support( AMP_Theme_Support::SLUG );
 This will serve templates in AMP-first, allowing you to use AMP components in your theme templates. If you want to make available in transitional mode, where templates are served in AMP or non-AMP documents, do:
      add_theme_support( AMP_Theme_Support::SLUG, array(          &#039;paired&#039; =&gt; true,      ) );
 Transitional mode is also implied if you define a template_dir:
      add_theme_support( AMP_Theme_Support::SLUG, array(          &#039;template_dir&#039; =&gt; &#039;amp&#039;,      ) );
 If you want to have AMP-specific templates in addition to serving AMP-first, do:
      add_theme_support( AMP_Theme_Support::SLUG, array(          &#039;paired&#039;       =&gt; false,          &#039;template_dir&#039; =&gt; &#039;amp&#039;,      ) );
 If you want to force AMP to always be served on a given template, you can use the templates_supported arg, for example to always serve the Category template in AMP:
      add_theme_support( AMP_Theme_Support::SLUG, array(          &#039;templates_supported&#039; =&gt; array(              &#039;is_category&#039; =&gt; true,          ),      ) );
 Or if you want to force AMP to be used on all templates:
      add_theme_support( AMP_Theme_Support::SLUG, array(          &#039;templates_supported&#039; =&gt; &#039;all&#039;,      ) );

### Return value

`boolean` - Whether this is in AMP &#039;canonical&#039; mode, that is whether it is AMP-first and there is not a separate (paired) AMP URL.

### Source

:link: [includes/amp-helper-functions.php:368](/includes/amp-helper-functions.php#L368-L370)

<details>
<summary>Show Code</summary>

```php
function amp_is_canonical() {
	return AMP_Theme_Support::STANDARD_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT );
}
```

</details>
