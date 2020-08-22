## Class `AmpProject\AmpWP\Exception\FailedToMakeInstance`

Exception thrown when the injector couldn&#039;t instantiate a given class or interface.

### Methods
<details>
<summary>`for_circular_reference`</summary>

```php
static public for_circular_reference( $interface_or_class )
```

Create a new instance of the exception for an interface or class that created a circular reference.


</details>
<details>
<summary>`for_unresolved_interface`</summary>

```php
static public for_unresolved_interface( $interface )
```

Create a new instance of the exception for an interface that could not be resolved to an instantiable class.


</details>
<details>
<summary>`for_unreflectable_class`</summary>

```php
static public for_unreflectable_class( $interface_or_class )
```

Create a new instance of the exception for an interface or class that could not be reflected upon.


</details>
<details>
<summary>`for_unresolved_argument`</summary>

```php
static public for_unresolved_argument( $argument_name, $class )
```

Create a new instance of the exception for an argument that could not be resolved.


</details>
<details>
<summary>`for_uninstantiated_shared_instance`</summary>

```php
static public for_uninstantiated_shared_instance( $class )
```

Create a new instance of the exception for a class that was meant to be reused but was not yet instantiated.


</details>
<details>
<summary>`for_invalid_delegate`</summary>

```php
static public for_invalid_delegate( $class )
```

Create a new instance of the exception for a delegate that was requested for a class that doesn&#039;t have one.


</details>
