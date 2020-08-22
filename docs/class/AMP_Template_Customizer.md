## Class `AMP_Template_Customizer`

AMP class that implements a template style editor in the Customizer.

A direct, formed link to the AMP editor in the Customizer is added via {@see amp_customizer_editor_link()} as a submenu to the Appearance menu.

### Methods
<details>
<summary>`__construct`</summary>

```php
protected __construct( \WP_Customize_Manager $wp_customize, ReaderThemeLoader $reader_theme_loader )
```

AMP_Template_Customizer constructor.


</details>
<details>
<summary>`init`</summary>

```php
static public init( \WP_Customize_Manager $wp_customize )
```

Initialize the template Customizer feature class.


</details>
<details>
<summary>`set_refresh_setting_transport`</summary>

```php
protected set_refresh_setting_transport()
```

Force changes to header video to cause refresh since there are various JS dependencies that prevent selective refresh from working properly.

In the AMP Customizer preview, selective refresh partial for `custom_header` will render &lt;amp-video&gt; or &lt;amp-youtube&gt; elements. Nevertheless, custom-header.js in core is not expecting AMP components. Therefore the `wp-custom-header-video-loaded` event never fires. This prevents themes from toggling the `has-header-video` class on the body.
 Additionally, the Twenty Seventeen core theme (the only which supports header videos) has two separate scripts `twentyseventeen-global` and `twentyseventeen-skip-link-focus-fix` which are depended on for displaying the video, for example toggling the &#039;has-header-video&#039; class when the video is added or removed.
 This applies whenever AMP is being served in the Customizer preview, that is, in Standard mode or Reader mode with a Reader theme.


</details>
<details>
<summary>`remove_cover_template_section`</summary>

```php
protected remove_cover_template_section()
```

Remove the Cover Template section if needed.

Prevent showing the &quot;Cover Template&quot; section if the active (non-Reader) theme does not have the same template as Twenty Twenty, as otherwise the user would be shown a section that would never reflect any preview change.


</details>
<details>
<summary>`remove_homepage_settings_section`</summary>

```php
protected remove_homepage_settings_section()
```

Remove the Homepage Settings section in the AMP Customizer for a Reader theme if needed.

The Homepage Settings section exclusively contains controls for options which apply to both AMP and non-AMP. If this is the case and there are no other controls added to it, then remove the section. Otherwise, the controls will all get the same notice added to them.


</details>
<details>
<summary>`init_legacy_preview`</summary>

```php
public init_legacy_preview()
```

Init Customizer preview for legacy.


</details>
<details>
<summary>`register_legacy_ui`</summary>

```php
public register_legacy_ui()
```

Sets up the AMP Customizer preview.


</details>
<details>
<summary>`get_amp_panel_description`</summary>

```php
protected get_amp_panel_description()
```

Get AMP panel description.

This is also added to the root panel description in the AMP Customizer when a Reader theme is being customized.


</details>
<details>
<summary>`register_legacy_settings`</summary>

```php
public register_legacy_settings()
```

Registers settings for customizing Legacy Reader AMP templates.


</details>
<details>
<summary>`add_customizer_scripts`</summary>

```php
public add_customizer_scripts()
```

Load up AMP scripts needed for Customizer integrations when a Reader theme has been selected.


</details>
<details>
<summary>`store_modified_theme_mod_setting_timestamps`</summary>

```php
public store_modified_theme_mod_setting_timestamps()
```

Store the timestamps for modified theme settings.

This is used to determine which settings from the Active theme should be presented for importing into the Reader theme. If a setting has been modified more recently in the Reader theme, then it doesn&#039;t make much sense to offer for the user to re-import a customization they already made.


</details>
<details>
<summary>`get_active_theme_import_settings`</summary>

```php
protected get_active_theme_import_settings()
```

Get settings to import from the active theme.


</details>
<details>
<summary>`render_setting_import_section_template`</summary>

```php
public render_setting_import_section_template()
```

Render template for the setting import &quot;section&quot;.

This section only has a menu item and it is not intended to expand.


</details>
<details>
<summary>`add_legacy_customizer_scripts`</summary>

```php
public add_legacy_customizer_scripts()
```

Load up AMP scripts needed for Customizer integrations in Legacy Reader mode.


</details>
<details>
<summary>`enqueue_legacy_preview_scripts`</summary>

```php
public enqueue_legacy_preview_scripts()
```

Enqueues scripts used in both the AMP and non-AMP Customizer preview (only applies to Legacy Reader mode).


</details>
<details>
<summary>`add_legacy_customize_preview_styles`</summary>

```php
public add_legacy_customize_preview_styles()
```

Add AMP Customizer preview styles for Legacy Reader mode.


</details>
<details>
<summary>`add_legacy_preview_scripts`</summary>

```php
public add_legacy_preview_scripts()
```

Enqueues Legacy Reader scripts and does wp_print_footer_scripts() so we can output customizer scripts.

This breaks AMP validation in the customizer but is necessary for the live preview.


</details>
<details>
<summary>`print_legacy_controls_templates`</summary>

```php
public print_legacy_controls_templates()
```

Print templates needed for AMP in Customizer (for Legacy Reader mode).


</details>
<details>
<summary>`is_amp_customizer`</summary>

```php
static public is_amp_customizer()
```

Whether the Customizer is AMP. This is always true since the AMP Customizer has been merged with the main Customizer.


</details>
