## Filter `amp_native_post_form_allowed`

```php
apply_filters( 'amp_native_post_form_allowed', $use_native );
```

Filters whether to allow native `POST` forms without conversion to use the `action-xhr` attribute and use the amp-form component.

### Arguments

* `bool $use_native` - Whether to allow native `POST` forms.

### Source

:link: [includes/amp-helper-functions.php:1430](/includes/amp-helper-functions.php#L1430)

<details>
<summary>Show Code</summary>

```php
return (bool) apply_filters( 'amp_native_post_form_allowed', false );
```

</details>
