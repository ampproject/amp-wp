## Class `AmpProject\AmpWP\RemoteRequest\CachedResponse`

Serializable object that represents a cached response together with its expiry time.

### Methods
<details>
<summary><code>__construct</code></summary>

```php
public __construct( $body, $headers, $status_code, DateTimeInterface $expiry )
```

Instantiate a CachedResponse object.


</details>
<details>
<summary><code>get_body</code></summary>

```php
public get_body()
```

Get the cached body.


</details>
<details>
<summary><code>get_headers</code></summary>

```php
public get_headers()
```

Get the cached headers.


</details>
<details>
<summary><code>get_status_code</code></summary>

```php
public get_status_code()
```

Get the cached status code.


</details>
<details>
<summary><code>is_valid</code></summary>

```php
public is_valid()
```

Determine the validity of the cached response.


</details>
<details>
<summary><code>get_expiry</code></summary>

```php
public get_expiry()
```

Get the expiry of the cached value.


</details>
<details>
<summary><code>is_expired</code></summary>

```php
public is_expired()
```

Check whether the cached value is expired.


</details>
