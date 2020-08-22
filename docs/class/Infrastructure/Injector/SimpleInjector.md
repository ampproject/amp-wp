## Class `AmpProject\AmpWP\Infrastructure\Injector\SimpleInjector`

A simplified implementation of a dependency injector.

### Methods
<details>
<summary>`__construct`</summary>

```php
public __construct( Instantiator $instantiator = null )
```

Instantiate a SimpleInjector object.


</details>
<details>
<summary>`make`</summary>

```php
public make( $interface_or_class, $arguments = array() )
```

Make an object instance out of an interface or class.


</details>
<details>
<summary>`bind`</summary>

```php
public bind( $from, $to )
```

Bind a given interface or class to an implementation.

Note: The implementation can be an interface as well, as long as it can be resolved to an instantiatable class at runtime.


</details>
<details>
<summary>`bind_argument`</summary>

```php
public bind_argument( $interface_or_class, $argument_name, $value )
```

Bind an argument for a class to a specific value.


</details>
<details>
<summary>`share`</summary>

```php
public share( $interface_or_class )
```

Always reuse and share the same instance for the provided interface or class.


</details>
<details>
<summary>`delegate`</summary>

```php
public delegate( $interface_or_class, callable $callable )
```

Delegate instantiation of an interface or class to a callable.


</details>
<details>
<summary>`make_dependency`</summary>

```php
private make_dependency( \AmpProject\AmpWP\Infrastructure\Injector\InjectionChain $injection_chain, $interface_or_class )
```

Make an object instance out of an interface or class.


</details>
<details>
<summary>`resolve`</summary>

```php
private resolve( \AmpProject\AmpWP\Infrastructure\Injector\InjectionChain $injection_chain, $interface_or_class )
```

Recursively resolve an interface to the class it should be bound to.


</details>
<details>
<summary>`get_dependencies_for`</summary>

```php
private get_dependencies_for( \AmpProject\AmpWP\Infrastructure\Injector\InjectionChain $injection_chain, ReflectionClass $reflection, $arguments = array() )
```

Get the array of constructor dependencies for a given reflected class.


</details>
<details>
<summary>`ensure_is_instantiable`</summary>

```php
private ensure_is_instantiable( ReflectionClass $reflection )
```

Ensure that a given reflected class is instantiable.


</details>
<details>
<summary>`resolve_argument`</summary>

```php
private resolve_argument( \AmpProject\AmpWP\Infrastructure\Injector\InjectionChain $injection_chain, $class, ReflectionParameter $parameter, $arguments )
```

Resolve a given reflected argument.


</details>
<details>
<summary>`resolve_argument_by_name`</summary>

```php
private resolve_argument_by_name( $class, ReflectionParameter $parameter, $arguments )
```

Resolve a given reflected argument by its name.


</details>
<details>
<summary>`has_shared_instance`</summary>

```php
private has_shared_instance( $class )
```

Check whether a shared instance exists for a given class.


</details>
<details>
<summary>`get_shared_instance`</summary>

```php
private get_shared_instance( $class )
```

Get the shared instance for a given class.


</details>
<details>
<summary>`has_delegate`</summary>

```php
private has_delegate( $class )
```

Check whether a delegate exists for a given class.


</details>
<details>
<summary>`get_delegate`</summary>

```php
private get_delegate( $class )
```

Get the delegate for a given class.


</details>
<details>
<summary>`get_class_reflection`</summary>

```php
private get_class_reflection( $class )
```

Get the reflection for a class or throw an exception.


</details>
