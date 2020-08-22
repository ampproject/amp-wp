## Class `AmpProject\AmpWP\ReaderThemeLoader`

Switches to the designated Reader theme when template mode enabled and when requesting an AMP page.

This class does not implement Conditional because other services need to always be able to access this service in order to determine whether or a Reader theme is loaded, and if so, what the previously-active theme was.

### Methods
<details>
<summary>`is_enabled`</summary>

```php
public is_enabled()
```

Is Reader mode with a Reader theme selected.


</details>
<details>
<summary>`is_theme_overridden`</summary>

```php
public is_theme_overridden()
```

Whether the active theme was overridden with the reader theme.


</details>
<details>
<summary>`is_amp_request`</summary>

```php
public is_amp_request()
```

Is an AMP request.


</details>
<details>
<summary>`register`</summary>

```php
public register()
```

Register the service with the system.


</details>
<details>
<summary>`filter_wp_prepare_themes_to_indicate_reader_theme`</summary>

```php
public filter_wp_prepare_themes_to_indicate_reader_theme( $prepared_themes )
```

Filter themes for JS to remove action to delete the selected Reader theme and show a notice.


</details>
<details>
<summary>`inject_theme_single_template_modifications`</summary>

```php
public inject_theme_single_template_modifications()
```

Inject new logic into the Backbone templates for rendering a theme lightbox.

This is admittedly hacky, but WordPress doesn&#039;t provide a much better option.


</details>
<details>
<summary>`get_reader_theme`</summary>

```php
public get_reader_theme()
```

Get reader theme.

If the Reader template mode is enabled


</details>
<details>
<summary>`get_active_theme`</summary>

```php
public get_active_theme()
```

Get active theme.

The theme that was active before switching to the Reader theme.


</details>
<details>
<summary>`override_theme`</summary>

```php
public override_theme()
```

Switch theme if in Reader mode, a Reader theme was selected, and the AMP query var is present.

Note that AMP_Theme_Support will redirect to the non-AMP version if AMP is not available for the query.


</details>
<details>
<summary>`disable_widgets`</summary>

```php
public disable_widgets()
```

Disable widgets.


</details>
<details>
<summary>`customize_previewable_devices`</summary>

```php
public customize_previewable_devices( $devices )
```

Make tablet (smartphone) the default device when opening AMP Customizer.


</details>
<details>
<summary>`remove_customizer_themes_panel`</summary>

```php
public remove_customizer_themes_panel( WP_Customize_Manager $wp_customize )
```

Remove themes panel from AMP Customizer.


</details>
