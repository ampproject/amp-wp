## Class `AmpProject\AmpWP\RemoteRequest\CachedResponse`

Serializable object that represents a cached response together with its expiry time.

### Methods
* `__construct`

<details>

```php
public __construct( $body, $headers, $status_code, DateTimeInterface $expiry )
```

Instantiate a CachedResponse object.


</details>
* `get_body`

<details>

```php
public get_body()
```

Get the cached body.


</details>
* `get_headers`

<details>

```php
public get_headers()
```

Get the cached headers.


</details>
* `get_status_code`

<details>

```php
public get_status_code()
```

Get the cached status code.


</details>
* `is_valid`

<details>

```php
public is_valid()
```

Determine the validity of the cached response.


</details>
* `get_expiry`

<details>

```php
public get_expiry()
```

Get the expiry of the cached value.


</details>
* `is_expired`

<details>

```php
public is_expired()
```

Check whether the cached value is expired.


</details>
