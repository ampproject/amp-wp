## Action `amp_print_analytics`

```php
do_action( 'amp_print_analytics', $analytics_entries );
```

Triggers before analytics entries are printed as amp-analytics tags.

This is useful for printing additional `amp-analytics` tags to the page without having to refactor any existing markup generation logic to use the data structure mutated by the `amp_analytics_entries` filter. For such cases, this action should be used for printing `amp-analytics` tags as opposed to using the `wp_footer` and `amp_post_template_footer` actions.

### Arguments

* `array $analytics_entries` - Analytics entries, already potentially modified by the amp_analytics_entries filter.

### Source

:link: [includes/amp-helper-functions.php:1344](/includes/amp-helper-functions.php#L1344)

<details>
<summary>Show Code</summary>

```php
do_action( 'amp_print_analytics', $analytics_entries );
```

</details>
