## Class `AMP_Validation_Callback_Wrapper`

Class AMP_Validation_Callback_Wrapper

### Methods
* `__construct`

<details>

```php
public __construct( $callback )
```

AMP_Validation_Callback_Wrapper constructor.


</details>
* `prepare`

<details>

```php
protected prepare( $args )
```

Prepare for invocation.


</details>
* `__invoke`

<details>

```php
public __invoke( $args )
```

Invoke wrapped callback.


</details>
* `invoke_with_first_ref_arg`

<details>

```php
public invoke_with_first_ref_arg( $first_arg, $other_args )
```

Invoke wrapped callback with first argument passed by reference.


</details>
* `finalize`

<details>

```php
protected finalize( array $preparation )
```

Finalize invocation.


</details>
* `finalize_styles`

<details>

```php
protected finalize_styles( \WP_Styles $wp_styles, array $before_registered, array $before_enqueued, array $before_extras )
```

Finalize styles after invocation.


</details>
* `finalize_scripts`

<details>

```php
protected finalize_scripts( \WP_Scripts $wp_scripts, array $before_registered, array $before_enqueued, array $before_extras )
```

Finalize scripts after invocation.


</details>
* `offsetSet`

<details>

```php
public offsetSet( $offset, $value )
```

Offset set.


</details>
* `offsetExists`

<details>

```php
public offsetExists( $offset )
```

Offset exists.


</details>
* `offsetUnset`

<details>

```php
public offsetUnset( $offset )
```

Offset unset.


</details>
* `offsetGet`

<details>

```php
public offsetGet( $offset )
```

Offset get.


</details>
