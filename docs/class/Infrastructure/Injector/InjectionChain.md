## Class `AmpProject\AmpWP\Infrastructure\Injector\InjectionChain`

The injection chain is similar to a trace, keeping track of what we have done so far and at what depth within the auto-wiring we currently are.

It is used to detect circular dependencies, and can also be dumped for debugging information.

### Methods
<details>
<summary><code>add_to_chain</code></summary>

```php
public add_to_chain( $class )
```

Add class to injection chain.


</details>
<details>
<summary><code>add_resolution</code></summary>

```php
public add_resolution( $resolution )
```

Add resolution for circular reference detection.


</details>
<details>
<summary><code>get_class</code></summary>

```php
public get_class()
```

Get the last class that was pushed to the injection chain.


</details>
<details>
<summary><code>get_chain</code></summary>

```php
public get_chain()
```

Get the injection chain.


</details>
<details>
<summary><code>has_resolution</code></summary>

```php
public has_resolution( $resolution )
```

Check whether the injection chain already has a given resolution.


</details>
