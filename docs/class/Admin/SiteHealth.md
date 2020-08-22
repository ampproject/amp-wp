## Class `AmpProject\AmpWP\Admin\SiteHealth`

Class SiteHealth

Adds tests and debugging information for Site Health.

### Methods
<details>
<summary><code>is_needed</code></summary>

```php
static public is_needed()
```

Check whether the conditional object is currently needed.


</details>
<details>
<summary><code>get_registration_action</code></summary>

```php
static public get_registration_action()
```

Get the action to use for registering the service.


</details>
<details>
<summary><code>__construct</code></summary>

```php
public __construct( MonitorCssTransientCaching $css_transient_caching )
```

SiteHealth constructor.


</details>
<details>
<summary><code>register</code></summary>

```php
public register()
```

Adds the filters.


</details>
<details>
<summary><code>add_tests</code></summary>

```php
public add_tests( $tests )
```

Adds Site Health tests related to this plugin.


</details>
<details>
<summary><code>get_persistent_object_cache_learn_more_action</code></summary>

```php
private get_persistent_object_cache_learn_more_action()
```

Get action HTML for the link to learn more about persistent object caching.


</details>
<details>
<summary><code>persistent_object_cache</code></summary>

```php
public persistent_object_cache()
```

Gets the test result data for whether there is a persistent object cache.


</details>
<details>
<summary><code>curl_multi_functions</code></summary>

```php
public curl_multi_functions()
```

Gets the test result data for whether the curl_multi_* functions exist.


</details>
<details>
<summary><code>icu_version</code></summary>

```php
public icu_version()
```

Gets the test result data for whether the proper ICU version is available.


</details>
<details>
<summary><code>css_transient_caching</code></summary>

```php
public css_transient_caching()
```

Gets the test result data for whether transient caching for stylesheets was disabled.


</details>
<details>
<summary><code>xdebug_extension</code></summary>

```php
public xdebug_extension()
```

Gets the test result data for whether the Xdebug extension is loaded.


</details>
<details>
<summary><code>add_debug_information</code></summary>

```php
public add_debug_information( $debugging_information )
```

Adds debug information for AMP.


</details>
<details>
<summary><code>modify_test_result</code></summary>

```php
public modify_test_result( $test_result )
```

Modify test results.


</details>
<details>
<summary><code>get_supported_templates</code></summary>

```php
private get_supported_templates()
```

Gets the templates that support AMP.


</details>
<details>
<summary><code>get_serve_all_templates</code></summary>

```php
private get_serve_all_templates()
```

Gets whether the option to serve all templates is selected.


</details>
<details>
<summary><code>get_css_transient_caching_disabled</code></summary>

```php
private get_css_transient_caching_disabled()
```

Gets whether the transient caching of stylesheets was disabled.


</details>
<details>
<summary><code>get_css_transient_caching_threshold</code></summary>

```php
private get_css_transient_caching_threshold()
```

Gets the threshold being used to when monitoring the transient caching of stylesheets.


</details>
<details>
<summary><code>get_css_transient_caching_sampling_range</code></summary>

```php
private get_css_transient_caching_sampling_range()
```

Gets the sampling range being used to when monitoring the transient caching of stylesheets.


</details>
<details>
<summary><code>add_extensions</code></summary>

```php
public add_extensions( $core_extensions )
```

Adds suggested PHP extensions to those that Core depends on.


</details>
<details>
<summary><code>add_styles</code></summary>

```php
public add_styles()
```

Add needed styles for the Site Health integration.


</details>
<details>
<summary><code>is_intl_extension_needed</code></summary>

```php
private is_intl_extension_needed()
```

Determine if the `intl` extension is needed.


</details>
