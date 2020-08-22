## Class `AmpProject\AmpWP\RemoteRequest\WpHttpRemoteGetRequest`

Remote request transport using the WordPress WP_Http abstraction layer.

### Methods
<details>
<summary><code>__construct</code></summary>

```php
public __construct( $ssl_verify = true, $timeout = self::DEFAULT_TIMEOUT, $retries = self::DEFAULT_RETRIES )
```

Instantiate a WpHttpRemoteGetRequest object.


</details>
<details>
<summary><code>get</code></summary>

```php
public get( $url )
```

Do a GET request to retrieve the contents of a remote URL.


</details>
