## Function `is_amp_endpoint`

```php
function is_amp_endpoint();
```

Determine whether the current response being served as AMP.

This function cannot be called before the parse_query action because it needs to be able to determine the queried object is able to be served as AMP. If &#039;amp&#039; theme support is not present, this function returns true just if the query var is present. If theme support is present, then it returns true in transitional mode if an AMP template is available and the query var is present, or else in standard mode if just the template is available.

### Source

[includes/amp-helper-functions.php:903](TODO)

<details>
<summary>Show Code</summary>

```php
<php ?>```

</details>
