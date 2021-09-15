## Filter `amp_enable_optimizer`

```php
apply_filters( 'amp_enable_optimizer', $enable_optimizer );
```

Filter whether the generated HTML output should be run through the AMP Optimizer or not.

### Arguments

* `bool $enable_optimizer` - Whether the generated HTML output should be run through the AMP Optimizer or not.

### Source

:link: [includes/class-amp-theme-support.php:2063](/includes/class-amp-theme-support.php#L2063)

<details>
<summary>Show Code</summary>

```php
$enable_optimizer = apply_filters( 'amp_enable_optimizer', $enable_optimizer );
```

</details>
