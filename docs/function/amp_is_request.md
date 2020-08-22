## Function `amp_is_request`

```php
function amp_is_request();
```

Determine whether the current request is for an AMP page.

This function cannot be called before the parse_query action because it needs to be able to determine the queried object is able to be served as AMP. If &#039;amp&#039; theme support is not present, this function returns true just if the query var is present. If theme support is present, then it returns true in transitional mode if an AMP template is available and the query var is present, or else in standard mode if just the template is available.

