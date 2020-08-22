## Class `AmpProject\AmpWP\Admin\SiteHealth`

Class SiteHealth

Adds tests and debugging information for Site Health.

### Methods
<details>
<summary>`is_needed`</summary>

```php
static public is_needed()
```

Check whether the conditional object is currently needed.


</details>
<details>
<summary>`get_registration_action`</summary>

```php
static public get_registration_action()
```

Get the action to use for registering the service.


</details>
<details>
<summary>`__construct`</summary>

```php
public __construct( MonitorCssTransientCaching $css_transient_caching )
```

SiteHealth constructor.


</details>
<details>
<summary>`register`</summary>

```php
public register()
```

Adds the filters.


</details>
<details>
<summary>`add_tests`</summary>

```php
public add_tests( $tests )
```

Adds Site Health tests related to this plugin.


</details>
<details>
<summary>`get_persistent_object_cache_learn_more_action`</summary>

```php
private get_persistent_object_cache_learn_more_action()
```

Get action HTML for the link to learn more about persistent object caching.


</details>
<details>
<summary>`persistent_object_cache`</summary>

```php
public persistent_object_cache()
```

Gets the test result data for whether there is a persistent object cache.


</details>
<details>
<summary>`curl_multi_functions`</summary>

```php
public curl_multi_functions()
```

Gets the test result data for whether the curl_multi_* functions exist.


</details>
<details>
<summary>`icu_version`</summary>

```php
public icu_version()
```

Gets the test result data for whether the proper ICU version is available.


</details>
<details>
<summary>`css_transient_caching`</summary>

```php
public css_transient_caching()
```

Gets the test result data for whether transient caching for stylesheets was disabled.


</details>
<details>
<summary>`xdebug_extension`</summary>

```php
public xdebug_extension()
```

Gets the test result data for whether the Xdebug extension is loaded.


</details>
<details>
<summary>`add_debug_information`</summary>

```php
public add_debug_information( $debugging_information )
```

Adds debug information for AMP.


</details>
<details>
<summary>`modify_test_result`</summary>

```php
public modify_test_result( $test_result )
```

Modify test results.


</details>
<details>
<summary>`get_supported_templates`</summary>

```php
private get_supported_templates()
```

Gets the templates that support AMP.


</details>
<details>
<summary>`get_serve_all_templates`</summary>

```php
private get_serve_all_templates()
```

Gets whether the option to serve all templates is selected.


</details>
<details>
<summary>`get_css_transient_caching_disabled`</summary>

```php
private get_css_transient_caching_disabled()
```

Gets whether the transient caching of stylesheets was disabled.


</details>
<details>
<summary>`get_css_transient_caching_threshold`</summary>

```php
private get_css_transient_caching_threshold()
```

Gets the threshold being used to when monitoring the transient caching of stylesheets.


</details>
<details>
<summary>`get_css_transient_caching_sampling_range`</summary>

```php
private get_css_transient_caching_sampling_range()
```

Gets the sampling range being used to when monitoring the transient caching of stylesheets.


</details>
<details>
<summary>`add_extensions`</summary>

```php
public add_extensions( $core_extensions )
```

Adds suggested PHP extensions to those that Core depends on.


</details>
<details>
<summary>`add_styles`</summary>

```php
public add_styles()
```

Add needed styles for the Site Health integration.


</details>
<details>
<summary>`is_intl_extension_needed`</summary>

```php
private is_intl_extension_needed()
```

Determine if the `intl` extension is needed.


</details>
