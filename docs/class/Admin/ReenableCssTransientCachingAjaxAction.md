## Class `AmpProject\AmpWP\Admin\ReenableCssTransientCachingAjaxAction`

Base class to define a new AJAX action.

### Methods
<details>
<summary>`register`</summary>

```php
public register()
```

Register the AJAX action with the WordPress system.


</details>
<details>
<summary>`register_ajax_script`</summary>

```php
public register_ajax_script( $hook_suffix )
```

Register the AJAX logic.


</details>
<details>
<summary>`reenable_css_transient_caching`</summary>

```php
public reenable_css_transient_caching()
```

Re-enable the CSS Transient caching.

This is triggered via an AJAX call from the Site Health panel.


</details>
