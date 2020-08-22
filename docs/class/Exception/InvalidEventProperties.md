## Class `AmpProject\AmpWP\Exception\InvalidEventProperties`

Exception thrown when an invalid properties are added to an Event.

### Methods
<details>
<summary><code>from_invalid_type</code></summary>

```php
static public from_invalid_type( $properties )
```

Create a new instance of the exception for a properties value that has the wrong type.


</details>
<details>
<summary><code>from_invalid_element_key_type</code></summary>

```php
static public from_invalid_element_key_type( $property )
```

Create a new instance of the exception for a properties value that has the wrong key type for one or more of its elements.


</details>
<details>
<summary><code>from_invalid_element_value_type</code></summary>

```php
static public from_invalid_element_value_type( $property )
```

Create a new instance of the exception for a properties value that has the wrong value type for one or more of its elements.


</details>
