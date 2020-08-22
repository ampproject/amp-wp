## Class `AmpProject\AmpWP\RemoteRequest\CachedResponse`

Serializable object that represents a cached response together with its expiry time.

### Methods
<details>
<summary>`__construct`</summary>

```php
public __construct( $body, $headers, $status_code, DateTimeInterface $expiry )
```

Instantiate a CachedResponse object.


</details>
<details>
<summary>`get_body`</summary>

```php
public get_body()
```

Get the cached body.


</details>
<details>
<summary>`get_headers`</summary>

```php
public get_headers()
```

Get the cached headers.


</details>
<details>
<summary>`get_status_code`</summary>

```php
public get_status_code()
```

Get the cached status code.


</details>
<details>
<summary>`is_valid`</summary>

```php
public is_valid()
```

Determine the validity of the cached response.


</details>
<details>
<summary>`get_expiry`</summary>

```php
public get_expiry()
```

Get the expiry of the cached value.


</details>
<details>
<summary>`is_expired`</summary>

```php
public is_expired()
```

Check whether the cached value is expired.


</details>
