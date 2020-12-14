## Filter `amp_mobile_version_switcher_link_text`

```php
apply_filters( 'amp_mobile_version_switcher_link_text', $text );
```

Filters the text to be used in the mobile switcher link.

Use the `amp_is_request()` function to determine whether you are filtering the text for the link to go to the non-AMP version or the AMP version.

### Arguments

* `string $text` - Link text to display.

### Source

:link: [src/MobileRedirection.php:460](/src/MobileRedirection.php#L460)

<details>
<summary>Show Code</summary>

```php
$text = apply_filters( 'amp_mobile_version_switcher_link_text', $text );
```

</details>
