## Class `AmpProject\AmpWP\Infrastructure\ServiceContainer\LazilyInstantiatedService`

A service that only gets properly instantiated when it is actually being retrieved from the container.

### Methods
<details>
<summary><code>__construct</code></summary>

```php
public __construct( callable $instantiation )
```

Instantiate a LazilyInstantiatedService object.


</details>
<details>
<summary><code>instantiate</code></summary>

```php
public instantiate()
```

Do the actual service instantiation and return the real service.


</details>
