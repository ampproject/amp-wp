## Class `AmpProject\AmpWP\Infrastructure\ServiceContainer\LazilyInstantiatedService`

A service that only gets properly instantiated when it is actually being retrieved from the container.

### Methods
* `__construct`

	<details>

	```php
	public __construct( callable $instantiation )
	```

	Instantiate a LazilyInstantiatedService object.


	</details>
* `instantiate`

	<details>

	```php
	public instantiate()
	```

	Do the actual service instantiation and return the real service.


	</details>
