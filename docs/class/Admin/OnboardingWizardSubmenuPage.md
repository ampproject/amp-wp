## Class `AmpProject\AmpWP\Admin\OnboardingWizardSubmenuPage`

AMP setup wizard submenu page class.

### Methods
<details>
<summary><code>__construct</code></summary>

```php
public __construct( \AmpProject\AmpWP\Admin\GoogleFonts $google_fonts, \AmpProject\AmpWP\Admin\ReaderThemes $reader_themes, \AmpProject\AmpWP\Admin\RESTPreloader $rest_preloader )
```

OnboardingWizardSubmenuPage constructor.


</details>
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
<summary><code>register</code></summary>

```php
public register()
```

Sets up hooks.


</details>
<details>
<summary><code>override_title</code></summary>

```php
public override_title( $admin_title )
```

Overrides the admin title on the wizard screen. Without this filter, the title portion would be empty.


</details>
<details>
<summary><code>override_template</code></summary>

```php
public override_template()
```

Renders the setup wizard screen output and exits.


</details>
<details>
<summary><code>render</code></summary>

```php
public render()
```

Renders the setup wizard screen output, beginning just before the closing head tag.


</details>
<details>
<summary><code>screen_handle</code></summary>

```php
public screen_handle()
```

Provides the setup screen handle.


</details>
<details>
<summary><code>enqueue_assets</code></summary>

```php
public enqueue_assets( $hook_suffix )
```

Enqueues setup assets.


</details>
<details>
<summary><code>add_preload_rest_paths</code></summary>

```php
protected add_preload_rest_paths()
```

Adds REST paths to preload.


</details>
