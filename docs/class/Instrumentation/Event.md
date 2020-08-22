## Class `AmpProject\AmpWP\Instrumentation\Event`

A server-timing event.

### Methods
<details>
<summary>`__construct`</summary>

```php
public __construct( $name, $description = null, $properties = array() )
```

Event constructor.


</details>
<details>
<summary>`get_name`</summary>

```php
public get_name()
```

Get the name of the event.


</details>
<details>
<summary>`get_description`</summary>

```php
public get_description()
```

Get the description of the event.


</details>
<details>
<summary>`add_properties`</summary>

```php
public add_properties( $properties )
```

Add additional properties to the event.


</details>
<details>
<summary>`get_header_string`</summary>

```php
public get_header_string()
```

Get the server timing header string.


</details>
