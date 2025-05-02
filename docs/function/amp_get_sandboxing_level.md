## Function `amp_get_sandboxing_level`

```php
function amp_get_sandboxing_level();
```

Determine sandboxing level if enabled.

### Return value

`int` - Following values are possible:             0: Sandbox is disabled.             1: Sandboxing level: Loose.             2: Sandboxing level: Moderate.             3: Sandboxing level: Strict.

### Source

:link: [includes/amp-helper-functions.php:2142](/includes/amp-helper-functions.php#L2142-L2147)

<details>
<summary>Show Code</summary>

```php
function amp_get_sandboxing_level() {
	if ( ! AMP_Options_Manager::get_option( Option::SANDBOXING_ENABLED ) ) {
		return 0;
	}
	return AMP_Options_Manager::get_option( Option::SANDBOXING_LEVEL );
}
```

</details>
