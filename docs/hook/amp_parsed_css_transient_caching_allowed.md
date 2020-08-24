## Hook `amp_parsed_css_transient_caching_allowed`

### Source

[includes/amp-helper-functions.php:1593](https://github.com/ampproject/amp-wp/blob/develop/includes/amp-helper-functions.php#L1593)

<details>
<summary>Show Code</summary>
```php
$sanitizers['AMP_Style_Sanitizer']['allow_transient_caching'] = apply_filters( 'amp_parsed_css_transient_caching_allowed', true );```
</details>
