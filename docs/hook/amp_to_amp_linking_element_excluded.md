## Hook `amp_to_amp_linking_element_excluded`

### Source

:link: [includes/sanitizers/class-amp-link-sanitizer.php:182](../../includes/sanitizers/class-amp-link-sanitizer.php#L182)

<details>
<summary>Show Code</summary>

```php
$excluded = (bool) apply_filters( 'amp_to_amp_linking_element_excluded', $excluded, $url, $rel, $element );
```

</details>
