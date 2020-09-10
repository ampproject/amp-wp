## Filter `amp_dev_tools_user_default_enabled`

```php
apply_filters( 'amp_dev_tools_user_default_enabled', $enabled, $user_id );
```

Filters whether Developer Tools is enabled by default for a user.

When Reader mode is active, Developer Tools is currently disabled by default.

### Arguments

* `bool $enabled` - DevTools enabled.
* `int $user_id` - User ID.

### Source

:link: [src/Admin/DevToolsUserAccess.php:93](/src/Admin/DevToolsUserAccess.php#L93)

<details>
<summary>Show Code</summary>

```php
$enabled = (bool) apply_filters( 'amp_dev_tools_user_default_enabled', $enabled, $user->ID );
```

</details>
