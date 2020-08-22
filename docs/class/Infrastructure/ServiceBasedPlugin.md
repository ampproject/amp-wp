## Class `AmpProject\AmpWP\Infrastructure\ServiceBasedPlugin`

This abstract base plugin provides all the boilerplate code for working with the dependency injector and the service container.

### Methods
<details>
<summary>`__construct`</summary>

```php
public __construct( $enable_filters = null, \AmpProject\AmpWP\Infrastructure\Injector $injector = null, \AmpProject\AmpWP\Infrastructure\ServiceContainer $service_container = null )
```

Instantiate a Theme object.


</details>
<details>
<summary>`activate`</summary>

```php
public activate( $network_wide )
```

Activate the plugin.


</details>
<details>
<summary>`deactivate`</summary>

```php
public deactivate( $network_wide )
```

Deactivate the plugin.


</details>
<details>
<summary>`register`</summary>

```php
public register()
```

Register the plugin with the WordPress system.


</details>
<details>
<summary>`register_services`</summary>

```php
public register_services()
```

Register the individual services of this plugin.


</details>
<details>
<summary>`validate_services`</summary>

```php
protected validate_services( $services, $fallback )
```

Validates the services array to make sure it is in a usable shape.

As the array of services could be filtered, we need to ensure it is always in a state where it doesn&#039;t throw PHP warnings or errors.


</details>
<details>
<summary>`get_identifier_from_fqcn`</summary>

```php
protected get_identifier_from_fqcn( $fqcn )
```

Generate a valid identifier for a provided FQCN.


</details>
<details>
<summary>`register_service`</summary>

```php
protected register_service( $id, $class )
```

Register a single service.


</details>
<details>
<summary>`get_container`</summary>

```php
public get_container()
```

Get the service container that contains the services that make up the plugin.


</details>
<details>
<summary>`instantiate_service`</summary>

```php
protected instantiate_service( $class )
```

Instantiate a single service.


</details>
<details>
<summary>`configure_injector`</summary>

```php
protected configure_injector( \AmpProject\AmpWP\Infrastructure\Injector $injector )
```

Configure the provided injector.

This method defines the mappings that the injector knows about, and the logic it requires to make more complex instantiations work.
 For more complex plugins, this should be extracted into a separate object or into configuration files.


</details>
<details>
<summary>`get_service_classes`</summary>

```php
protected get_service_classes()
```

Get the list of services to register.


</details>
<details>
<summary>`get_bindings`</summary>

```php
protected get_bindings()
```

Get the bindings for the dependency injector.

The bindings let you map interfaces (or classes) to the classes that should be used to implement them.


</details>
<details>
<summary>`get_arguments`</summary>

```php
protected get_arguments()
```

Get the argument bindings for the dependency injector.

The argument bindings let you map specific argument values for specific classes.


</details>
<details>
<summary>`get_shared_instances`</summary>

```php
protected get_shared_instances()
```

Get the shared instances for the dependency injector.

These classes will only be instantiated once by the injector and then reused on subsequent requests.
 This effectively turns them into singletons, without any of the drawbacks of the actual Singleton anti-pattern.


</details>
<details>
<summary>`get_delegations`</summary>

```php
protected get_delegations()
```

Get the delegations for the dependency injector.

These are basically factories to provide custom instantiation logic for classes.


</details>
<details>
<summary>`maybe_resolve`</summary>

```php
protected maybe_resolve( $value )
```

Maybe resolve a value that is a callable instead of a scalar.

Values that are passed through this method can optionally be provided as callables instead of direct values and will be evaluated when needed.


</details>
