## Class `AmpProject\AmpWP\Instrumentation\EventWithDuration`

A server-timing event with a duration.

### Methods
<details>
<summary><code>__construct</code></summary>

```php
public __construct( $name, $description = null, $properties = array(), $duration = 0.0 )
```

Event constructor.


</details>
<details>
<summary><code>set_duration</code></summary>

```php
public set_duration( $duration )
```

Set the event duration.


</details>
<details>
<summary><code>get_duration</code></summary>

```php
public get_duration()
```

Get the event duration.


</details>
<details>
<summary><code>get_header_string</code></summary>

```php
public get_header_string()
```

Get the server timing header string.


</details>
