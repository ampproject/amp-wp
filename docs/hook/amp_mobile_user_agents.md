## Filter `amp_mobile_user_agents`

```php
apply_filters( 'amp_mobile_user_agents', $user_agents );
```

Filters the list of user agents used to determine if the user agent from the current request is a mobile one.

### Arguments

* `string[] $user_agents` - List of mobile user agent search strings (and regex patterns).

### Source

:link: [src/MobileRedirection.php:324](/src/MobileRedirection.php#L324)

<details>
<summary>Show Code</summary>

```php
return apply_filters( 'amp_mobile_user_agents', $default_user_agents );
```

</details>
