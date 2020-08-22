## Function `amp_force_query_var_value`

```php
function amp_force_query_var_value( $query_vars );
```

Make sure the `amp` query var has an explicit value.

This avoids issues when filtering the deprecated `query_string` hook.

### Arguments

* `array $query_vars` - Query vars.

