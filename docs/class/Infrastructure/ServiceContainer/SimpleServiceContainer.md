## Class `AmpProject\AmpWP\Infrastructure\ServiceContainer\SimpleServiceContainer`

A simplified implementation of a service container.

We extend ArrayObject so we have default implementations for iterators and array access.

### Methods
<details>
<summary>`get`</summary>

```php
public get( $id )
```

Find a service of the container by its identifier and return it.


</details>
<details>
<summary>`has`</summary>

```php
public has( $id )
```

Check whether the container can return a service for the given identifier.


</details>
<details>
<summary>`put`</summary>

```php
public put( $id, Service $service )
```

Put a service into the container for later retrieval.


</details>
