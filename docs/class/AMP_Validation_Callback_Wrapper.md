## Class `AMP_Validation_Callback_Wrapper`

Class AMP_Validation_Callback_Wrapper

### Methods
<details>
<summary><code>__construct</code></summary>

```php
public __construct( $callback )
```

AMP_Validation_Callback_Wrapper constructor.


</details>
<details>
<summary><code>prepare</code></summary>

```php
protected prepare( $args )
```

Prepare for invocation.


</details>
<details>
<summary><code>__invoke</code></summary>

```php
public __invoke( $args )
```

Invoke wrapped callback.


</details>
<details>
<summary><code>invoke_with_first_ref_arg</code></summary>

```php
public invoke_with_first_ref_arg( $first_arg, $other_args )
```

Invoke wrapped callback with first argument passed by reference.


</details>
<details>
<summary><code>finalize</code></summary>

```php
protected finalize( array $preparation )
```

Finalize invocation.


</details>
<details>
<summary><code>finalize_styles</code></summary>

```php
protected finalize_styles( \WP_Styles $wp_styles, array $before_registered, array $before_enqueued, array $before_extras )
```

Finalize styles after invocation.


</details>
<details>
<summary><code>finalize_scripts</code></summary>

```php
protected finalize_scripts( \WP_Scripts $wp_scripts, array $before_registered, array $before_enqueued, array $before_extras )
```

Finalize scripts after invocation.


</details>
<details>
<summary><code>offsetSet</code></summary>

```php
public offsetSet( $offset, $value )
```

Offset set.


</details>
<details>
<summary><code>offsetExists</code></summary>

```php
public offsetExists( $offset )
```

Offset exists.


</details>
<details>
<summary><code>offsetUnset</code></summary>

```php
public offsetUnset( $offset )
```

Offset unset.


</details>
<details>
<summary><code>offsetGet</code></summary>

```php
public offsetGet( $offset )
```

Offset get.


</details>
