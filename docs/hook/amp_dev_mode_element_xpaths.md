## Filter `amp_dev_mode_element_xpaths`

```php
apply_filters( 'amp_dev_mode_element_xpaths', $element_xpaths );
```

Filters the XPath queries for elements that should be enabled for dev mode.

By supplying XPath queries to this filter, the data-ampdevmode attribute will automatically be added to the root HTML element as well as to any elements that match the expressions. The attribute is added to the elements prior to running any of the sanitizers.

### Arguments

* `string[] $element_xpaths` - XPath element queries. Context is the root element.

### Source

:link: [includes/amp-helper-functions.php:1568](/includes/amp-helper-functions.php#L1568)

<details>
<summary>Show Code</summary>

```php
$dev_mode_xpaths = (array) apply_filters( 'amp_dev_mode_element_xpaths', [] );
```

</details>
