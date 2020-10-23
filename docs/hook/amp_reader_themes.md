## Filter `amp_reader_themes`

```php
apply_filters( 'amp_reader_themes', $themes );
```

Filters supported reader themes.

### Arguments

* `array $themes` - [     Reader theme data.     {         @type string         $name           Theme name.         @type string         $slug           Theme slug.         @type string         $slug           URL of theme preview.         @type string         $screenshot_url The URL of a mobile screenshot. Note: if this is empty, the theme may not display.         @type string         $homepage       A link to a page with more information about the theme.         @type string         $description    A description of the theme.         @type string|boolean $requires       Minimum version of WordPress required by the theme. False if all versions are supported.         @type string|boolean $requires_php   Minimum version of PHP required by the theme. False if all versions are supported.         @type string         $download_link  A link to the theme&#039;s zip file. If empty, the plugin will attempt to download the theme from wordpress.org.     } ]

### Source

:link: [src/Admin/ReaderThemes.php:127](/src/Admin/ReaderThemes.php#L127)

<details>
<summary>Show Code</summary>

```php
$themes = (array) apply_filters( 'amp_reader_themes', $themes );
```

</details>
