## Class `AmpProject\AmpWP\Admin\SiteHealth`

Class SiteHealth

Adds tests and debugging information for Site Health.

### Methods
* `is_needed`

<details>

```php
static public is_needed()
```

Check whether the conditional object is currently needed.


</details>
* `get_registration_action`

<details>

```php
static public get_registration_action()
```

Get the action to use for registering the service.


</details>
* `__construct`

<details>

```php
public __construct( MonitorCssTransientCaching $css_transient_caching )
```

SiteHealth constructor.


</details>
* `register`

<details>

```php
public register()
```

Adds the filters.


</details>
* `add_tests`

<details>

```php
public add_tests( $tests )
```

Adds Site Health tests related to this plugin.


</details>
* `get_persistent_object_cache_learn_more_action`

<details>

```php
private get_persistent_object_cache_learn_more_action()
```

Get action HTML for the link to learn more about persistent object caching.


</details>
* `persistent_object_cache`

<details>

```php
public persistent_object_cache()
```

Gets the test result data for whether there is a persistent object cache.


</details>
* `curl_multi_functions`

<details>

```php
public curl_multi_functions()
```

Gets the test result data for whether the curl_multi_* functions exist.


</details>
* `icu_version`

<details>

```php
public icu_version()
```

Gets the test result data for whether the proper ICU version is available.


</details>
* `css_transient_caching`

<details>

```php
public css_transient_caching()
```

Gets the test result data for whether transient caching for stylesheets was disabled.


</details>
* `xdebug_extension`

<details>

```php
public xdebug_extension()
```

Gets the test result data for whether the Xdebug extension is loaded.


</details>
* `add_debug_information`

<details>

```php
public add_debug_information( $debugging_information )
```

Adds debug information for AMP.


</details>
* `modify_test_result`

<details>

```php
public modify_test_result( $test_result )
```

Modify test results.


</details>
* `get_supported_templates`

<details>

```php
private get_supported_templates()
```

Gets the templates that support AMP.


</details>
* `get_serve_all_templates`

<details>

```php
private get_serve_all_templates()
```

Gets whether the option to serve all templates is selected.


</details>
* `get_css_transient_caching_disabled`

<details>

```php
private get_css_transient_caching_disabled()
```

Gets whether the transient caching of stylesheets was disabled.


</details>
* `get_css_transient_caching_threshold`

<details>

```php
private get_css_transient_caching_threshold()
```

Gets the threshold being used to when monitoring the transient caching of stylesheets.


</details>
* `get_css_transient_caching_sampling_range`

<details>

```php
private get_css_transient_caching_sampling_range()
```

Gets the sampling range being used to when monitoring the transient caching of stylesheets.


</details>
* `add_extensions`

<details>

```php
public add_extensions( $core_extensions )
```

Adds suggested PHP extensions to those that Core depends on.


</details>
* `add_styles`

<details>

```php
public add_styles()
```

Add needed styles for the Site Health integration.


</details>
* `is_intl_extension_needed`

<details>

```php
private is_intl_extension_needed()
```

Determine if the `intl` extension is needed.


</details>
