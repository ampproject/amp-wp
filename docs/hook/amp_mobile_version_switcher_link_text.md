## Filter `amp_mobile_version_switcher_link_text`


Filters the text to be used in the mobile switcher link.

Use the `amp_is_request()` function to determine whether you are filtering the text for the link to go to the non-AMP version or the AMP version.

### Source

:link: [src/MobileRedirection.php:449](../../src/MobileRedirection.php#L449)

<details>
<summary>Show Code</summary>

```php
$text = apply_filters( 'amp_mobile_version_switcher_link_text', $text );
```

</details>
