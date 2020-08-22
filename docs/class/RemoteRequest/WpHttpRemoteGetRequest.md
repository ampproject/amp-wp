## Class `AmpProject\AmpWP\RemoteRequest\WpHttpRemoteGetRequest`

Remote request transport using the WordPress WP_Http abstraction layer.

### Methods
* `__construct`

<details>

```php
public __construct( $ssl_verify = true, $timeout = self::DEFAULT_TIMEOUT, $retries = self::DEFAULT_RETRIES )
```

Instantiate a WpHttpRemoteGetRequest object.


</details>
* `get`

<details>

```php
public get( $url )
```

Do a GET request to retrieve the contents of a remote URL.


</details>
