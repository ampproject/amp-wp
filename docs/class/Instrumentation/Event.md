## Class `AmpProject\AmpWP\Instrumentation\Event`

A server-timing event.

### Methods
<details>
<summary><code>__construct</code></summary>

```php
public __construct( $name, $description = null, $properties = array() )
```

Event constructor.


</details>
<details>
<summary><code>get_name</code></summary>

```php
public get_name()
```

Get the name of the event.


</details>
<details>
<summary><code>get_description</code></summary>

```php
public get_description()
```

Get the description of the event.


</details>
<details>
<summary><code>add_properties</code></summary>

```php
public add_properties( $properties )
```

Add additional properties to the event.


</details>
<details>
<summary><code>get_header_string</code></summary>

```php
public get_header_string()
```

Get the server timing header string.


</details>
