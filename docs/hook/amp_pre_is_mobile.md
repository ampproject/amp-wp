## Filter `amp_pre_is_mobile`

```php
apply_filters( 'amp_pre_is_mobile', $is_mobile );
```

Filters whether the current request is from a mobile device. This is provided as a means to short-circuit the normal determination of a mobile request below.

### Arguments

* `null $is_mobile` - Whether the current request is from a mobile device.

### Source

:link: [src/MobileRedirection.php:232](/src/MobileRedirection.php#L232)

<details>
<summary>Show Code</summary>

```php
$pre_is_mobile = apply_filters( 'amp_pre_is_mobile', null );
```

</details>
