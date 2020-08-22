## Class `AmpProject\AmpWP\AmpSlugCustomizationWatcher`

Service for redirecting mobile users to the AMP version of a page.

### Methods
* `register`

<details>

```php
public register()
```

Register.


</details>
* `did_customize_early`

<details>

```php
public did_customize_early()
```

Whether the slug was customized early (at plugins_loaded action, priority 8).


</details>
* `did_customize_late`

<details>

```php
public did_customize_late()
```

Whether the slug was customized early (at after_setup_theme action, priority 4).


</details>
* `determine_early_customization`

<details>

```php
public determine_early_customization()
```

Determine if the slug was customized early.

Early customization happens by plugins_loaded action at priority 8; this is required in order for the slug to be used by `ReaderThemeLoader::override_theme()` which runs at priority 9; this method in turn must run before before `_wp_customize_include()` which runs at plugins_loaded priority 10. At that point the current theme gets determined, so for Reader themes to apply the logic in `ReaderThemeLoader` must run beforehand.


</details>
* `determine_late_customization`

<details>

```php
public determine_late_customization()
```

Determine if the slug was defined late.

Late slug customization often happens when a theme itself defines `AMP_QUERY_VAR`. This is too late for the plugin to be able to offer Reader themes which must have `AMP_QUERY_VAR` defined by plugins_loaded priority 9. Also, defining `AMP_QUERY_VAR` is fundamentally incompatible since loading a Reader theme means preventing the original theme from ever being loaded, and thus the theme&#039;s customized `AMP_QUERY_VAR` will never be read.
 This method must run before `amp_after_setup_theme()` which runs at the after_setup_theme action priority 5. In this function, the `amp_get_slug()` function is called which will then set the query var for the remainder of the request.


</details>
