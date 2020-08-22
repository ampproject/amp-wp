## Class `AmpProject\AmpWP\Admin\Polyfills`

Registers assets that may not be available in the current site&#039;s version of core.

### Methods
<details>
<summary>`get_registration_action`</summary>

```php
static public get_registration_action()
```

Get the action to use for registering the service.


</details>
<details>
<summary>`register`</summary>

```php
public register()
```

Runs on instantiation.


</details>
<details>
<summary>`register_shimmed_scripts`</summary>

```php
public register_shimmed_scripts( $wp_scripts )
```

Registers scripts not guaranteed to be available in core.


</details>
<details>
<summary>`register_shimmed_styles`</summary>

```php
public register_shimmed_styles( $wp_styles )
```

Registers shimmed assets not guaranteed to be available in core.


</details>
