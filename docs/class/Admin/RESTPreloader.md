## Class `AmpProject\AmpWP\Admin\RESTPreloader`

Preloads REST responses for client-side applications to prevent having to call fetch on page load.

### Methods
<details>
<summary>`add_preloaded_path`</summary>

```php
public add_preloaded_path( $path )
```

Adds a REST path to be preloaded.


</details>
<details>
<summary>`preload_data`</summary>

```php
public preload_data()
```

Preloads data using apiFetch preloading middleware.


</details>
