## Class `AmpProject\AmpWP\Admin\OnboardingWizardSubmenuPage`

AMP setup wizard submenu page class.

### Methods
* `__construct`

<details>

```php
public __construct( \AmpProject\AmpWP\Admin\GoogleFonts $google_fonts, \AmpProject\AmpWP\Admin\ReaderThemes $reader_themes, \AmpProject\AmpWP\Admin\RESTPreloader $rest_preloader )
```

OnboardingWizardSubmenuPage constructor.


</details>
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
* `register`

<details>

```php
public register()
```

Sets up hooks.


</details>
* `override_title`

<details>

```php
public override_title( $admin_title )
```

Overrides the admin title on the wizard screen. Without this filter, the title portion would be empty.


</details>
* `override_template`

<details>

```php
public override_template()
```

Renders the setup wizard screen output and exits.


</details>
* `render`

<details>

```php
public render()
```

Renders the setup wizard screen output, beginning just before the closing head tag.


</details>
* `screen_handle`

<details>

```php
public screen_handle()
```

Provides the setup screen handle.


</details>
* `enqueue_assets`

<details>

```php
public enqueue_assets( $hook_suffix )
```

Enqueues setup assets.


</details>
* `add_preload_rest_paths`

<details>

```php
protected add_preload_rest_paths()
```

Adds REST paths to preload.


</details>
