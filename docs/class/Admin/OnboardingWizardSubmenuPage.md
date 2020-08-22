## Class `AmpProject\AmpWP\Admin\OnboardingWizardSubmenuPage`

AMP setup wizard submenu page class.

### Methods
<details>
<summary>`__construct`</summary>

```php
public __construct( \AmpProject\AmpWP\Admin\GoogleFonts $google_fonts, \AmpProject\AmpWP\Admin\ReaderThemes $reader_themes, \AmpProject\AmpWP\Admin\RESTPreloader $rest_preloader )
```

OnboardingWizardSubmenuPage constructor.


</details>
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
<summary>`register`</summary>

```php
public register()
```

Sets up hooks.


</details>
<details>
<summary>`override_title`</summary>

```php
public override_title( $admin_title )
```

Overrides the admin title on the wizard screen. Without this filter, the title portion would be empty.


</details>
<details>
<summary>`override_template`</summary>

```php
public override_template()
```

Renders the setup wizard screen output and exits.


</details>
<details>
<summary>`render`</summary>

```php
public render()
```

Renders the setup wizard screen output, beginning just before the closing head tag.


</details>
<details>
<summary>`screen_handle`</summary>

```php
public screen_handle()
```

Provides the setup screen handle.


</details>
<details>
<summary>`enqueue_assets`</summary>

```php
public enqueue_assets( $hook_suffix )
```

Enqueues setup assets.


</details>
<details>
<summary>`add_preload_rest_paths`</summary>

```php
protected add_preload_rest_paths()
```

Adds REST paths to preload.


</details>
