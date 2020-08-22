## Class `AmpProject\AmpWP\Infrastructure\ServiceContainer\SimpleServiceContainer`

A simplified implementation of a service container.

We extend ArrayObject so we have default implementations for iterators and array access.

### Methods
* `get`

<details>

```php
public get( $id )
```

Find a service of the container by its identifier and return it.


</details>
* `has`

<details>

```php
public has( $id )
```

Check whether the container can return a service for the given identifier.


</details>
* `put`

<details>

```php
public put( $id, Service $service )
```

Put a service into the container for later retrieval.


</details>
