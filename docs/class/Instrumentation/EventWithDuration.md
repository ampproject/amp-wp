## Class `AmpProject\AmpWP\Instrumentation\EventWithDuration`

A server-timing event with a duration.

### Methods
<details>
<summary>`__construct`</summary>

```php
public __construct( $name, $description = null, $properties = array(), $duration = 0.0 )
```

Event constructor.


</details>
<details>
<summary>`set_duration`</summary>

```php
public set_duration( $duration )
```

Set the event duration.


</details>
<details>
<summary>`get_duration`</summary>

```php
public get_duration()
```

Get the event duration.


</details>
<details>
<summary>`get_header_string`</summary>

```php
public get_header_string()
```

Get the server timing header string.


</details>
