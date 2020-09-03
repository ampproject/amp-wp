## Hook `amp_dev_mode_element_xpaths`


Filters the XPath queries for elements that should be enabled for dev mode.

By supplying XPath queries to this filter, the data-ampdevmode attribute will automatically be added to the root HTML element as well as to any elements that match the expressions. The attribute is added to the elements prior to running any of the sanitizers.

### Source

:link: [includes/amp-helper-functions.php:1564](../../includes/amp-helper-functions.php#L1564)

<details>
<summary>Show Code</summary>

```php
$dev_mode_xpaths = (array) apply_filters( 'amp_dev_mode_element_xpaths', [] );
```

</details>
