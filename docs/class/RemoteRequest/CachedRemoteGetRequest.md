## Class `AmpProject\AmpWP\RemoteRequest\CachedRemoteGetRequest`

Caching decorator for RemoteGetRequest implementations.

Caching uses WordPress transients.

### Methods
<details>
<summary><code>__construct</code></summary>

```php
public __construct( RemoteGetRequest $remote_request, $expiry = MONTH_IN_SECONDS, $min_expiry = DAY_IN_SECONDS, $use_cache_control = true )
```

Instantiate a CachedRemoteGetRequest object.

This is a decorator that can wrap around an existing remote request object to add a caching layer.


</details>
<details>
<summary><code>get</code></summary>

```php
public get( $url )
```

Do a GET request to retrieve the contents of a remote URL.


</details>
<details>
<summary><code>get_expiry_time</code></summary>

```php
private get_expiry_time( Response $response )
```

Get the expiry time of the data to cache.

This will use the cache-control header information in the provided response or fall back to the provided default expiry.


</details>
<details>
<summary><code>get_max_age</code></summary>

```php
private get_max_age( $cache_control_strings )
```

Get the max age setting from one or more cache-control header strings.


</details>
