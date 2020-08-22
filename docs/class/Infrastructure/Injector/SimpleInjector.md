## Class `AmpProject\AmpWP\Infrastructure\Injector\SimpleInjector`

A simplified implementation of a dependency injector.

### Methods
* `__construct`

	<details>

	```php
	public __construct( Instantiator $instantiator = null )
	```

	Instantiate a SimpleInjector object.


	</details>
* `make`

	<details>

	```php
	public make( $interface_or_class, $arguments = array() )
	```

	Make an object instance out of an interface or class.


	</details>
* `bind`

	<details>

	```php
	public bind( $from, $to )
	```

	Bind a given interface or class to an implementation.

Note: The implementation can be an interface as well, as long as it can be resolved to an instantiatable class at runtime.


	</details>
* `bind_argument`

	<details>

	```php
	public bind_argument( $interface_or_class, $argument_name, $value )
	```

	Bind an argument for a class to a specific value.


	</details>
* `share`

	<details>

	```php
	public share( $interface_or_class )
	```

	Always reuse and share the same instance for the provided interface or class.


	</details>
* `delegate`

	<details>

	```php
	public delegate( $interface_or_class, callable $callable )
	```

	Delegate instantiation of an interface or class to a callable.


	</details>
* `make_dependency`

	<details>

	```php
	private make_dependency( \AmpProject\AmpWP\Infrastructure\Injector\InjectionChain $injection_chain, $interface_or_class )
	```

	Make an object instance out of an interface or class.


	</details>
* `resolve`

	<details>

	```php
	private resolve( \AmpProject\AmpWP\Infrastructure\Injector\InjectionChain $injection_chain, $interface_or_class )
	```

	Recursively resolve an interface to the class it should be bound to.


	</details>
* `get_dependencies_for`

	<details>

	```php
	private get_dependencies_for( \AmpProject\AmpWP\Infrastructure\Injector\InjectionChain $injection_chain, ReflectionClass $reflection, $arguments = array() )
	```

	Get the array of constructor dependencies for a given reflected class.


	</details>
* `ensure_is_instantiable`

	<details>

	```php
	private ensure_is_instantiable( ReflectionClass $reflection )
	```

	Ensure that a given reflected class is instantiable.


	</details>
* `resolve_argument`

	<details>

	```php
	private resolve_argument( \AmpProject\AmpWP\Infrastructure\Injector\InjectionChain $injection_chain, $class, ReflectionParameter $parameter, $arguments )
	```

	Resolve a given reflected argument.


	</details>
* `resolve_argument_by_name`

	<details>

	```php
	private resolve_argument_by_name( $class, ReflectionParameter $parameter, $arguments )
	```

	Resolve a given reflected argument by its name.


	</details>
* `has_shared_instance`

	<details>

	```php
	private has_shared_instance( $class )
	```

	Check whether a shared instance exists for a given class.


	</details>
* `get_shared_instance`

	<details>

	```php
	private get_shared_instance( $class )
	```

	Get the shared instance for a given class.


	</details>
* `has_delegate`

	<details>

	```php
	private has_delegate( $class )
	```

	Check whether a delegate exists for a given class.


	</details>
* `get_delegate`

	<details>

	```php
	private get_delegate( $class )
	```

	Get the delegate for a given class.


	</details>
* `get_class_reflection`

	<details>

	```php
	private get_class_reflection( $class )
	```

	Get the reflection for a class or throw an exception.


	</details>
