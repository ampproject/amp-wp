## Function `is_amp_endpoint`

> :warning: This function is deprecated: Use amp_is_request() instead.

```php
function is_amp_endpoint();
```

Determine whether the current response being served as AMP.

This function cannot be called before the parse_query action because it needs to be able to determine the queried object is able to be served as AMP. If 'amp' theme support is not present, this function returns true just if the query var is present. If theme support is present, then it returns true in transitional mode if an AMP template is available and the query var is present, or else in standard mode if just the template is available.

### Return value

`bool` - Whether it is the AMP endpoint.

### Source

:link: [includes/amp-helper-functions.php:909](/includes/amp-helper-functions.php#L909-L911)

<details>
<summary>Show Code</summary>

```php
function is_amp_endpoint() {
	return amp_is_request();
}
```

</details>
