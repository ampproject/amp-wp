## Class `AmpProject\AmpWP\Instrumentation\Event`

A server-timing event.

### Methods
* `__construct`

<details>

```php
public __construct( $name, $description = null, $properties = array() )
```

Event constructor.


</details>
* `get_name`

<details>

```php
public get_name()
```

Get the name of the event.


</details>
* `get_description`

<details>

```php
public get_description()
```

Get the description of the event.


</details>
* `add_properties`

<details>

```php
public add_properties( $properties )
```

Add additional properties to the event.


</details>
* `get_header_string`

<details>

```php
public get_header_string()
```

Get the server timing header string.


</details>
