## Function `amp_render_scripts`

```php
function amp_render_scripts( $scripts );
```

Generate HTML for AMP scripts that have not yet been printed.

This is adapted from `wp_scripts()-&gt;do_items()`, but it runs only the bare minimum required to output the missing scripts, without allowing other filters to apply which may cause an invalid AMP response. The HTML for the scripts is returned instead of being printed.

### Arguments

* `array $scripts` - Script handles mapped to URLs or true.

### Source

[includes/amp-helper-functions.php:1071](TODO)

<details>
<summary>Show Code</summary>
```php
<php ?>```
</details>
