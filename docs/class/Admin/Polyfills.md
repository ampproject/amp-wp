## Class `AmpProject\AmpWP\Admin\Polyfills`

Registers assets that may not be available in the current site&#039;s version of core.

### Methods
* `get_registration_action`

<details>

```php
static public get_registration_action()
```

Get the action to use for registering the service.


</details>
* `register`

<details>

```php
public register()
```

Runs on instantiation.


</details>
* `register_shimmed_scripts`

<details>

```php
public register_shimmed_scripts( $wp_scripts )
```

Registers scripts not guaranteed to be available in core.


</details>
* `register_shimmed_styles`

<details>

```php
public register_shimmed_styles( $wp_styles )
```

Registers shimmed assets not guaranteed to be available in core.


</details>
